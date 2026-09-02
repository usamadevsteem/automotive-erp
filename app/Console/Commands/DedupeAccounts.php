<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\JournalEntryLine;
use App\Models\Platform\Tenant;
use Illuminate\Console\Command;

class DedupeAccounts extends Command
{
    protected $signature = 'accounts:dedupe {--force : Actually apply the changes (default is dry-run)}';
    protected $description = 'Merge duplicate Chart of Accounts entries (created by an extra demo seeder) into the official accounts the app relies on';

    /**
     * Maps [duplicate_code => official_code]. Only codes that are genuinely
     * separate duplicate rows are listed here — codes that were already
     * shared between seeders (1100, 1200, 2100, 5100) are not included
     * since no duplicate row exists for them.
     */
    private array $mapping = [
        '1000' => '1001', // Cash in Hand
        '1010' => '1002', // Bank Account
        '3000' => '3001', // Owner's Equity
        '3100' => '3002', // Retained Earnings
        '4000' => '4001', // Vehicle Sales Revenue
        '4100' => '4002', // Commission Income
        '5000' => '5001', // Cost of Goods Sold
        '5200' => '5102', // Rent Expense
        '5300' => '5103', // Utilities Expense
        '5400' => '5104', // Marketing
        '5900' => '5100', // General & Admin Expense
    ];

    public function handle(): int
    {
        $tenant = Tenant::where('subdomain', 'demo')->first();
        if (!$tenant) {
            $this->error('No tenant with subdomain "demo" found.');
            return 1;
        }

        app()->instance('tenant', $tenant);

        $dryRun = !$this->option('force');
        if ($dryRun) {
            $this->warn('DRY RUN — no changes will be made. Re-run with --force to apply.');
        }

        $this->table(['Duplicate Code', 'Duplicate Name', 'Merges Into', 'Official Name', 'Journal Lines to Move'], collect($this->mapping)->map(function ($officialCode, $dupeCode) {
            $dupe    = Account::where('account_code', $dupeCode)->first();
            $official = Account::where('account_code', $officialCode)->first();

            return [
                $dupeCode,
                $dupe?->account_name ?? '(not found — skipped)',
                $officialCode,
                $official?->account_name ?? '(missing! skipped)',
                $dupe ? JournalEntryLine::where('account_id', $dupe->id)->count() : '—',
            ];
        })->values()->toArray());

        if ($dryRun) {
            return 0;
        }

        $merged = 0;
        foreach ($this->mapping as $dupeCode => $officialCode) {
            $dupe     = Account::where('account_code', $dupeCode)->first();
            $official = Account::where('account_code', $officialCode)->first();

            if (!$dupe || !$official) {
                continue;
            }

            $moved = JournalEntryLine::where('account_id', $dupe->id)->update(['account_id' => $official->id]);
            $dupe->delete();
            $merged++;

            $this->info("Merged [{$dupeCode}] {$dupe->account_name} → [{$officialCode}] {$official->account_name} ({$moved} journal lines moved)");
        }

        $this->info("Done. {$merged} duplicate accounts merged and removed.");
        return 0;
    }
}
