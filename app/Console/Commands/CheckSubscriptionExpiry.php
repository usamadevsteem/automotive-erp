<?php

namespace App\Console\Commands;

use App\Models\Platform\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckSubscriptionExpiry extends Command
{
    protected $signature   = 'subscriptions:check-expiry';
    protected $description = 'Notify tenants whose subscription is expiring soon, and suspend those fully expired';

    public function handle(): int
    {
        $warned    = 0;
        $suspended = 0;

        // Warn tenants expiring in 7 days
        Tenant::where('status', 'active')
            ->whereDate('plan_expires_at', today()->addDays(7))
            ->each(function (Tenant $tenant) use (&$warned) {
                // In production: Mail::to($tenant->email)->send(new SubscriptionExpiringSoon($tenant));
                $this->line("Reminder: {$tenant->company_name} subscription expires in 7 days.");
                $warned++;
            });

        // Suspend tenants past 7-day grace period
        Tenant::where('status', 'active')
            ->whereDate('plan_expires_at', '<', today()->subDays(7))
            ->each(function (Tenant $tenant) use (&$suspended) {
                $tenant->update(['status' => 'suspended']);
                $suspended++;
            });

        $this->info("Warned {$warned} tenants, suspended {$suspended} tenants.");
        return self::SUCCESS;
    }
}
