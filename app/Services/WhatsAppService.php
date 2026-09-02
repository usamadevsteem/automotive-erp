<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\WhatsappAccount;
use App\Models\WhatsappConversation;
use App\Models\WhatsappMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private function getAccount(): ?WhatsappAccount
    {
        return WhatsappAccount::active()->first();
    }

    // ── Send a text message ────────────────────────────────────────

    public function sendText(WhatsappConversation $conversation, string $text): ?WhatsappMessage
    {
        $account = $this->getAccount();
        if (!$account) return null;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $account->api_key,
            'Content-Type'  => 'application/json',
        ])->post(
            config('services.whatsapp.base_url', 'https://graph.facebook.com')
            . '/' . config('services.whatsapp.api_version', 'v19.0')
            . "/{$account->phone_number}/messages",
            [
                'messaging_product' => 'whatsapp',
                'to'                => $conversation->customer_phone,
                'type'              => 'text',
                'text'              => ['body' => $text],
            ]
        );

        if ($response->failed()) {
            Log::error('WhatsApp send failed', ['response' => $response->json()]);
            return null;
        }

        $waMessageId = $response->json('messages.0.id');

        $message = WhatsappMessage::create([
            'tenant_id'       => $conversation->tenant_id,
            'conversation_id' => $conversation->id,
            'wa_message_id'   => $waMessageId,
            'direction'       => 'outbound',
            'message_type'    => 'text',
            'content'         => $text,
            'status'          => 'sent',
            'sent_by'         => auth()->id(),
            'sent_at'         => now(),
        ]);

        // Update conversation preview
        $conversation->update([
            'last_message_at'      => now(),
            'last_message_preview' => substr($text, 0, 200),
        ]);

        return $message;
    }

    // ── Handle incoming webhook ────────────────────────────────────

    public function handleWebhook(array $payload): void
    {
        $entries = $payload['entry'] ?? [];

        foreach ($entries as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                $value    = $change['value'] ?? [];
                $messages = $value['messages'] ?? [];

                foreach ($messages as $msg) {
                    $this->processInboundMessage($msg, $value);
                }

                // Handle status updates (delivered, read)
                foreach ($value['statuses'] ?? [] as $status) {
                    $this->processStatusUpdate($status);
                }
            }
        }
    }

    private function processInboundMessage(array $msg, array $value): void
    {
        $account = WhatsappAccount::where('phone_number', $value['metadata']['phone_number_id'] ?? '')->first();
        if (!$account) return;

        $phone       = $msg['from'];
        $waMessageId = $msg['id'];

        // Idempotency check
        if (WhatsappMessage::where('wa_message_id', $waMessageId)->exists()) return;

        // Find or create conversation
        $conversation = WhatsappConversation::firstOrCreate(
            ['tenant_id' => $account->tenant_id, 'wa_account_id' => $account->id, 'customer_phone' => $phone],
            [
                'customer_name'        => $value['contacts'][0]['profile']['name'] ?? null,
                'status'               => 'open',
                'last_message_at'      => now(),
                'last_message_preview' => '',
            ]
        );

        // Try to link to existing customer
        if (!$conversation->customer_id) {
            $customer = Customer::where('mobile', $phone)
                ->orWhere('mobile_alt', $phone)
                ->first();
            if ($customer) {
                $conversation->update(['customer_id' => $customer->id]);
            }
        }

        $content = match($msg['type']) {
            'text'     => $msg['text']['body'] ?? '',
            'image'    => '[Image]',
            'document' => '[Document: ' . ($msg['document']['filename'] ?? 'file') . ']',
            'audio'    => '[Voice Message]',
            'video'    => '[Video]',
            default    => '[' . ucfirst($msg['type']) . ']',
        };

        WhatsappMessage::create([
            'tenant_id'       => $account->tenant_id,
            'conversation_id' => $conversation->id,
            'wa_message_id'   => $waMessageId,
            'direction'       => 'inbound',
            'message_type'    => $msg['type'],
            'content'         => $content,
            'status'          => 'delivered',
            'sent_at'         => now(),
        ]);

        $conversation->update([
            'last_message_at'      => now(),
            'last_message_preview' => substr($content, 0, 200),
            'unread_count'         => $conversation->unread_count + 1,
        ]);
    }

    private function processStatusUpdate(array $status): void
    {
        $map = ['delivered' => 'delivered', 'read' => 'read', 'failed' => 'failed'];

        WhatsappMessage::where('wa_message_id', $status['id'])
            ->update([
                'status'       => $map[$status['status']] ?? 'sent',
                'delivered_at' => $status['status'] === 'delivered' ? now() : null,
                'read_at'      => $status['status'] === 'read'      ? now() : null,
            ]);
    }

    // ── Convert conversation to lead ───────────────────────────────

    public function convertToLead(WhatsappConversation $conversation, array $data): Lead
    {
        $lead = Lead::create([
            'tenant_id'       => $conversation->tenant_id,
            'branch_id'       => auth()->user()->branch_id,
            'customer_id'     => $conversation->customer_id,
            'full_name'       => $data['full_name'] ?? $conversation->customer_name ?? $conversation->customer_phone,
            'phone'           => $conversation->customer_phone,
            'source'          => 'whatsapp',
            'vehicle_interest'=> $data['vehicle_interest'] ?? null,
            'notes'           => "Created from WhatsApp conversation.",
            'status'          => 'new',
            'assigned_to'     => auth()->id(),
            'created_by'      => auth()->id(),
        ]);

        $conversation->update(['lead_id' => $lead->id]);

        return $lead;
    }
}
