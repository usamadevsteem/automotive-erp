<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Branch;
use App\Models\Commission;
use App\Models\CommissionRule;
use App\Models\InstallmentPlan;
use App\Models\InstallmentSchedule;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Payment;
use App\Models\Platform\Tenant;
use App\Models\SaleInvoice;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class FinanceDemoSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('subdomain', 'demo')->first();

        if (!$tenant) {
            $this->command->error('No tenant with subdomain "demo" found.');
            return;
        }

        app()->instance('tenant', $tenant);
        setPermissionsTeamId($tenant->id);

        $admin = User::where('tenant_id', $tenant->id)->where('is_active', true)->first();
        if (!$admin) {
            $this->command->error('No active user found for this tenant.');
            return;
        }
        Auth::login($admin);

        $branch = Branch::first();
        if (!$branch) {
            $this->command->error('No branch found.');
            return;
        }

        $this->command->info('Seeding finance data for tenant: ' . $tenant->company_name);

        // ── Chart of Accounts ────────────────────────────────────────
        $accounts = $this->seedChartOfAccounts();
        $this->command->info('  Created ' . count($accounts) . ' accounts.');

        // ── Journal Entries (from existing sale invoices & expenses) ─
        $jeCount = $this->seedJournalEntries($branch, $admin, $accounts);
        $this->command->info("  Created {$jeCount} journal entries.");

        // ── Installment Plan (for one installment-type invoice) ──────
        $planCount = $this->seedInstallments($admin);
        $this->command->info("  Created {$planCount} installment plan(s).");

        // ── Commission Rules & Commissions ────────────────────────────
        $commissionCount = $this->seedCommissions($admin);
        $this->command->info("  Created {$commissionCount} commissions.");

        $this->command->info('Finance demo data seeding complete.');
    }

    private function seedChartOfAccounts(): array
    {
        $chart = [
            // Assets
            ['1000', 'Cash in Hand',            'asset',     null],
            ['1010', 'Bank Account',             'asset',     null],
            ['1100', 'Accounts Receivable',      'asset',     null],
            ['1200', 'Vehicle Inventory',        'asset',     null],
            // Liabilities
            ['2000', 'Accounts Payable',         'liability', null],
            ['2100', 'Loans Payable',            'liability', null],
            // Equity
            ['3000', "Owner's Equity",           'equity',    null],
            ['3100', 'Retained Earnings',        'equity',    null],
            // Revenue
            ['4000', 'Vehicle Sales Revenue',    'revenue',   null],
            ['4100', 'Commission Income',        'revenue',   null],
            // Expense
            ['5000', 'Cost of Goods Sold',       'expense',   null],
            ['5100', 'Salaries & Wages',         'expense',   null],
            ['5200', 'Rent Expense',             'expense',   null],
            ['5300', 'Utilities Expense',        'expense',   null],
            ['5400', 'Marketing & Advertising',  'expense',   null],
            ['5500', 'Vehicle Repair & Detailing','expense',  null],
            ['5900', 'General & Admin Expense',  'expense',   null],
        ];

        $created = [];
        foreach ($chart as [$code, $name, $type, $parentId]) {
            $created[$code] = Account::firstOrCreate(
                ['account_code' => $code],
                [
                    'account_name' => $name,
                    'account_type' => $type,
                    'parent_id'    => $parentId,
                    'is_system'    => true,
                    'is_active'    => true,
                ]
            );
        }

        return $created;
    }

    private function seedJournalEntries(Branch $branch, User $admin, array $accounts): int
    {
        $count = 0;
        $cash    = $accounts['1000'];
        $bank    = $accounts['1010'];
        $revenue = $accounts['4000'];
        $cogs    = $accounts['5000'];
        $inventory = $accounts['1200'];

        // One journal entry per sale invoice: Dr Cash/Bank, Cr Revenue
        $invoices = SaleInvoice::with('vehicle')->get();
        foreach ($invoices as $i => $invoice) {
            if ($invoice->amount_paid <= 0) continue;

            $je = JournalEntry::create([
                'branch_id'      => $branch->id,
                'entry_number'   => 'JE-' . now()->year . '-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'entry_date'     => $invoice->invoice_date,
                'narration'      => 'Sale of vehicle - Invoice ' . $invoice->invoice_number,
                'reference_type' => 'sale_invoice',
                'reference_id'   => $invoice->id,
                'entry_type'     => 'sale',
                'total_debit'    => $invoice->amount_paid,
                'total_credit'   => $invoice->amount_paid,
                'status'         => 'posted',
                'is_auto'        => true,
                'created_by'     => $admin->id,
                'posted_at'      => $invoice->invoice_date,
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $je->id,
                'account_id'       => $bank->id,
                'debit_amount'     => $invoice->amount_paid,
                'credit_amount'    => 0,
                'description'      => 'Payment received - ' . $invoice->invoice_number,
            ]);
            JournalEntryLine::create([
                'journal_entry_id' => $je->id,
                'account_id'       => $revenue->id,
                'debit_amount'     => 0,
                'credit_amount'    => $invoice->amount_paid,
                'description'      => 'Vehicle sale revenue - ' . $invoice->invoice_number,
            ]);
            $count++;

            // Matching COGS entry using the vehicle's total cost
            if ($invoice->vehicle) {
                $cost = (float) $invoice->vehicle->total_cost;
                if ($cost > 0) {
                    $jeCogs = JournalEntry::create([
                        'branch_id'      => $branch->id,
                        'entry_number'   => 'JE-' . now()->year . '-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT) . 'C',
                        'entry_date'     => $invoice->invoice_date,
                        'narration'      => 'Cost of goods sold - ' . $invoice->invoice_number,
                        'reference_type' => 'sale_invoice',
                        'reference_id'   => $invoice->id,
                        'entry_type'     => 'sale',
                        'total_debit'    => $cost,
                        'total_credit'   => $cost,
                        'status'         => 'posted',
                        'is_auto'        => true,
                        'created_by'     => $admin->id,
                        'posted_at'      => $invoice->invoice_date,
                    ]);

                    JournalEntryLine::create([
                        'journal_entry_id' => $jeCogs->id,
                        'account_id'       => $cogs->id,
                        'debit_amount'     => $cost,
                        'credit_amount'    => 0,
                        'description'      => 'COGS - ' . $invoice->invoice_number,
                    ]);
                    JournalEntryLine::create([
                        'journal_entry_id' => $jeCogs->id,
                        'account_id'       => $inventory->id,
                        'debit_amount'     => 0,
                        'credit_amount'    => $cost,
                        'description'      => 'Inventory reduction - ' . $invoice->invoice_number,
                    ]);
                    $count++;
                }
            }
        }

        // A handful of expense journal entries
        $expenseAccounts = [$accounts['5100'], $accounts['5200'], $accounts['5300'], $accounts['5400'], $accounts['5900']];
        foreach (range(1, 5) as $i) {
            $amount = rand(10, 60) * 1000;
            $acct   = $expenseAccounts[array_rand($expenseAccounts)];

            $je = JournalEntry::create([
                'branch_id'    => $branch->id,
                'entry_number' => 'JE-' . now()->year . '-' . str_pad((string) (900 + $i), 4, '0', STR_PAD_LEFT),
                'entry_date'   => now()->subDays(rand(1, 20)),
                'narration'    => 'Operating expense - ' . $acct->account_name,
                'entry_type'   => 'expense',
                'total_debit'  => $amount,
                'total_credit' => $amount,
                'status'       => 'posted',
                'is_auto'      => false,
                'created_by'   => $admin->id,
                'posted_at'    => now()->subDays(rand(1, 20)),
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $je->id,
                'account_id'       => $acct->id,
                'debit_amount'     => $amount,
                'credit_amount'    => 0,
                'description'      => $acct->account_name,
            ]);
            JournalEntryLine::create([
                'journal_entry_id' => $je->id,
                'account_id'       => $cash->id,
                'debit_amount'     => 0,
                'credit_amount'    => $amount,
                'description'      => 'Cash payment',
            ]);
            $count++;
        }

        // Opening balance entry
        $openingAmount = 5000000;
        $je = JournalEntry::create([
            'branch_id'    => $branch->id,
            'entry_number' => 'JE-' . now()->year . '-0001-OPEN',
            'entry_date'   => now()->subMonths(2),
            'narration'    => "Owner's opening capital investment",
            'entry_type'   => 'opening',
            'total_debit'  => $openingAmount,
            'total_credit' => $openingAmount,
            'status'       => 'posted',
            'is_auto'      => false,
            'created_by'   => $admin->id,
            'posted_at'    => now()->subMonths(2),
        ]);
        JournalEntryLine::create([
            'journal_entry_id' => $je->id,
            'account_id'       => $bank->id,
            'debit_amount'     => $openingAmount,
            'credit_amount'    => 0,
            'description'      => 'Opening capital',
        ]);
        JournalEntryLine::create([
            'journal_entry_id' => $je->id,
            'account_id'       => $accounts['3000']->id,
            'debit_amount'     => 0,
            'credit_amount'    => $openingAmount,
            'description'      => "Owner's equity",
        ]);
        $count++;

        return $count;
    }

    private function seedInstallments(User $admin): int
    {
        $invoice = SaleInvoice::where('payment_type', 'installment')->first();
        if (!$invoice) {
            $this->command->warn('  No installment-type invoice found, skipping installment plan.');
            return 0;
        }

        $downPayment    = $invoice->amount_paid;
        $financed       = $invoice->net_amount - $downPayment;
        $installmentCount = 6;
        $installmentAmt = round($financed / $installmentCount, -2);

        $plan = InstallmentPlan::create([
            'plan_number'         => 'IPL-' . now()->year . '-0001',
            'sale_invoice_id'     => $invoice->id,
            'customer_id'         => $invoice->customer_id,
            'vehicle_id'          => $invoice->vehicle_id,
            'total_sale_price'    => $invoice->net_amount,
            'down_payment'        => $downPayment,
            'financed_amount'     => $financed,
            'installment_count'   => $installmentCount,
            'installment_amount'  => $installmentAmt,
            'frequency'           => 'monthly',
            'first_due_date'      => now()->startOfMonth()->addMonth(),
            'late_fee_per_day'    => 500,
            'guarantor_name'      => 'Muhammad Aslam',
            'guarantor_cnic'      => '35202-1234567-9',
            'guarantor_phone'     => '03211234567',
            'status'              => 'active',
            'created_by'          => $admin->id,
        ]);

        for ($i = 1; $i <= $installmentCount; $i++) {
            $dueDate = now()->startOfMonth()->addMonths($i);
            $isPast  = $dueDate->isPast();
            $status  = 'pending';
            $paidAmount = 0;
            $paidDate = null;

            // First two installments already paid, third overdue, rest pending
            if ($i <= 2) {
                $status = 'paid';
                $paidAmount = $installmentAmt;
                $paidDate = $dueDate->copy()->subDays(rand(0, 3));
            } elseif ($i === 3 && $isPast) {
                $status = 'overdue';
            }

            InstallmentSchedule::create([
                'plan_id'             => $plan->id,
                'installment_number'  => $i,
                'due_date'            => $dueDate,
                'amount_due'          => $installmentAmt,
                'late_fee'            => $status === 'overdue' ? 2500 : 0,
                'total_due'           => $installmentAmt + ($status === 'overdue' ? 2500 : 0),
                'paid_amount'         => $paidAmount,
                'paid_date'           => $paidDate,
                'status'              => $status,
            ]);
        }

        return 1;
    }

    private function seedCommissions(User $admin): int
    {
        $rule = CommissionRule::firstOrCreate(
            ['name' => 'Standard Salesman Commission'],
            [
                'applies_to'  => 'salesman',
                'calc_type'   => 'percentage_profit',
                'value'       => 10, // 10% of profit
                'is_active'   => true,
                'created_by'  => $admin->id,
            ]
        );

        $count = 0;
        $invoices = SaleInvoice::with('vehicle')->get();

        foreach ($invoices as $invoice) {
            if (!$invoice->vehicle) continue;

            $profit = $invoice->net_amount - $invoice->vehicle->total_cost;
            $commissionAmount = round(max($profit, 0) * 0.10, 0);

            Commission::create([
                'sale_invoice_id'    => $invoice->id,
                'vehicle_id'         => $invoice->vehicle_id,
                'commission_rule_id' => $rule->id,
                'employee_id'        => $admin->id,
                'commission_type'    => 'salesman',
                'sale_amount'        => $invoice->net_amount,
                'profit_amount'      => $profit,
                'commission_amount'  => $commissionAmount,
                'status'             => ['pending','approved','paid'][array_rand([0,1,2])],
                'approved_by'        => $admin->id,
                'approved_at'        => now()->subDays(rand(1, 10)),
            ]);
            $count++;
        }

        return $count;
    }
}
