<?php

namespace App\Http\Controllers\WhatsApp;

use App\Http\Controllers\Controller;
use App\Models\WhatsappAccount;
use App\Models\WhatsappConversation;
use App\Models\WhatsappQuickReply;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class WhatsAppController extends Controller
{
    public function __construct(private readonly WhatsAppService $whatsAppService) {}

    // ── Shared Inbox ───────────────────────────────────────────────

    public function index(Request $request): View
    {
        $this->authorize('view-whatsapp');

        $conversations = WhatsappConversation::with(['customer','assignedTo','latestMessage'])
            ->when($request->status,   fn($q,$v) => $q->where('status',$v))
            ->when($request->assigned, fn($q,$v) => $v === 'me'
                ? $q->where('assigned_to', auth()->id())
                : $q->whereNull('assigned_to'))
            ->orderByDesc('last_message_at')
            ->paginate(30)->withQueryString();

        $quickReplies = WhatsappQuickReply::active()->orderBy('title')->get();
        $totalUnread  = WhatsappConversation::where('tenant_id', app('tenant')->id)
            ->where('unread_count', '>', 0)->count();

        $accounts = WhatsappAccount::active()->get();

        return view('whatsapp.index', compact('conversations','quickReplies','totalUnread','accounts'));
    }

    // ── Single Conversation ────────────────────────────────────────

    public function conversation(WhatsappConversation $conversation): View
    {
        $this->authorize('reply-whatsapp');

        $conversation->load(['customer','lead','assignedTo','messages.sentBy']);
        $conversation->markAllRead();

        $quickReplies = WhatsappQuickReply::active()->get();

        return view('whatsapp.conversation', compact('conversation','quickReplies'));
    }

    // ── Send Message ───────────────────────────────────────────────

    public function send(Request $request, WhatsappConversation $conversation): JsonResponse
    {
        $this->authorize('reply-whatsapp');

        $request->validate([
            'message' => ['required','string','max:4096'],
        ]);

        $message = $this->whatsAppService->sendText($conversation, $request->message);

        if (!$message) {
            return response()->json(['error' => 'Failed to send message.'], 500);
        }

        return response()->json([
            'success' => true,
            'message' => [
                'id'        => $message->id,
                'content'   => $message->content,
                'direction' => $message->direction,
                'sent_at'   => $message->sent_at->format('H:i'),
                'sent_by'   => auth()->user()->name,
            ],
        ]);
    }

    // ── Assign Conversation ────────────────────────────────────────

    public function assign(Request $request, WhatsappConversation $conversation): RedirectResponse
    {
        $this->authorize('reply-whatsapp');
        $request->validate(['assigned_to' => ['required','exists:users,id']]);

        $conversation->update([
            'assigned_to' => $request->assigned_to,
            'assigned_at' => now(),
            'status'      => 'assigned',
        ]);

        return back()->with('success', 'Conversation assigned.');
    }

    // ── Resolve Conversation ───────────────────────────────────────

    public function resolve(WhatsappConversation $conversation): RedirectResponse
    {
        $this->authorize('reply-whatsapp');
        $conversation->update(['status' => 'resolved']);
        return redirect()->route('whatsapp.index')->with('success', 'Conversation resolved.');
    }

    // ── Convert to Lead ────────────────────────────────────────────

    public function createLead(Request $request, WhatsappConversation $conversation): RedirectResponse
    {
        $this->authorize('create-leads');

        $data = $request->validate([
            'full_name'        => ['nullable','string','max:100'],
            'vehicle_interest' => ['nullable','string','max:200'],
        ]);

        $lead = $this->whatsAppService->convertToLead($conversation, $data);

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Lead created from WhatsApp conversation.');
    }

    // ── Webhook ────────────────────────────────────────────────────

    public function webhookVerify(Request $request): Response
    {
        $token     = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');
        $expected  = config('services.whatsapp.verify_token', env('WHATSAPP_VERIFY_TOKEN'));

        if ($token === $expected) {
            return response($challenge, 200);
        }

        return response('Unauthorized', 403);
    }

    public function webhookReceive(Request $request): Response
    {
        // Verify signature in production
        $this->whatsAppService->handleWebhook($request->all());
        return response('EVENT_RECEIVED', 200);
    }

    // ── Quick Replies ──────────────────────────────────────────────

    public function quickReplies(): View
    {
        $this->authorize('manage-whatsapp-settings');
        $replies = WhatsappQuickReply::orderByDesc('usage_count')->paginate(20);
        return view('whatsapp.quick-replies', compact('replies'));
    }

    public function storeQuickReply(Request $request): RedirectResponse
    {
        $this->authorize('manage-whatsapp-settings');
        $data = $request->validate([
            'title'    => ['required','string','max:100'],
            'body'     => ['required','string'],
            'category' => ['nullable','string','max:50'],
        ]);

        $data['created_by'] = auth()->id();
        WhatsappQuickReply::create($data);
        return back()->with('success', 'Quick reply saved.');
    }

    // ── Settings ───────────────────────────────────────────────────

    public function settings(): View
    {
        $this->authorize('manage-whatsapp-settings');
        $account = WhatsappAccount::first();
        return view('whatsapp.settings', compact('account'));
    }

    public function saveSettings(Request $request): RedirectResponse
    {
        $this->authorize('manage-whatsapp-settings');
        $data = $request->validate([
            'phone_number' => ['required','string','max:20'],
            'display_name' => ['nullable','string','max:100'],
            'provider'     => ['required','in:meta_cloud_api,twilio,waapi'],
            'api_key'      => ['nullable','string'],
            'webhook_token'=> ['nullable','string'],
        ]);

        WhatsappAccount::updateOrCreate(
            ['tenant_id' => app('tenant')->id],
            $data
        );

        return back()->with('success', 'WhatsApp settings saved.');
    }
}
