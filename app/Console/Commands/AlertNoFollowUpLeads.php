<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Models\Notification;
use App\Models\Platform\Tenant;
use Illuminate\Console\Command;

class AlertNoFollowUpLeads extends Command
{
    protected $signature   = 'leads:alert-no-followup';
    protected $description = 'Notify salesmen of leads that have not been contacted in 48 hours';

    public function handle(): int
    {
        $count = 0;

        Tenant::whereIn('status', ['active', 'trial'])->each(function (Tenant $tenant) use (&$count) {
            app()->instance('tenant', $tenant);

            $staleLeads = Lead::whereNotIn('status', ['won', 'lost'])
                ->where('created_at', '<=', now()->subHours(48))
                ->whereDoesntHave('activities', function ($q) {
                    $q->where('created_at', '>=', now()->subHours(48));
                })
                ->whereNotNull('assigned_to')
                ->get();

            foreach ($staleLeads as $lead) {
                Notification::create([
                    'tenant_id' => $tenant->id,
                    'user_id'   => $lead->assigned_to,
                    'type'      => 'lead_no_followup',
                    'title'     => 'Lead needs follow-up',
                    'message'   => "Lead '{$lead->full_name}' has not been contacted in 48 hours.",
                    'data'      => ['lead_id' => $lead->id],
                ]);
                $count++;
            }
        });

        $this->info("Created {$count} no-follow-up alerts.");
        return self::SUCCESS;
    }
}
