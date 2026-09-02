<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\SaleInvoice;
use App\Models\Payment;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    public function __construct(private readonly NumberingService $numbering) {}

    // ── Post journal entry ─────────────────────────────────────────

    public function postEntry(array $data, array $lines): JournalEntry
    {
        return DB::transaction(function () use ($data, $lines) {
            $totalDebit  = array_sum(array_column($lines, 'debit'));
            $totalCredit = array_sum(array_column($lines, 'credit'));

            if (abs($totalDebit - $totalCredit) > 0.01) {
                throw new \LogicException("Journal entry is unbalanced. Debit: {$totalDebit}, Credit: {$totalCredit}");
            }

            $entry = JournalEntry::create([
                'tenant_id'       => app('tenant')->id,
                'branch_id'       => $data['branch_id'],
                'entry_number'    => $this->numbering->journalEntry(),
                'entry_date'      => $data['entry_date'] ?? today(),
                'narration'       => $data['narration'],
                'reference_type'  => $data['reference_type'] ?? null,
                'reference_id'    => $data['reference_id']   ?? null,
                'entry_type'      => $data['entry_type']      ?? 'adjustment',
                'total_debit'     => $totalDebit,
                'total_credit'    => $totalCredit,
                'status'          => 'posted',
                'is_auto'         => $data['is_auto'] ?? false,
                'created_by'      => auth()->id(),
                'posted_at'       => now(),
            ]);

            foreach ($lines as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'tenant_id'        => app('tenant')->id,
                    'account_id'       => $line['account_id'],
                    'debit_amount'     => $line['debit']  ?? 0,
                    'credit_amount'    => $line['credit'] ?? 0,
                    'description'      => $line['description'] ?? null,
                ]);
            }

            return $entry;
        });
    }

    // ── Auto-post on vehicle sale ──────────────────────────────────

    public function postSaleEntry(SaleInvoice $invoice): JournalEntry
    {
        $accounts    = $this->getSystemAccounts();
        $branchId    = $invoice->branch_id;

        $lines = [
            // DR: Cash/Receivable (net amount)
            [
                'account_id'  => $accounts['cash_in_hand'],
                'debit'       => $invoice->net_amount,
                'credit'      => 0,
                'description' => "Sale: {$invoice->invoice_number}",
            ],
            // CR: Sales Revenue
            [
                'account_id'  => $accounts['sales_revenue'],
                'debit'       => 0,
                'credit'      => $invoice->sale_price,
                'description' => "Sale: {$invoice->invoice_number}",
            ],
            // DR: Cost of Goods Sold
            [
                'account_id'  => $accounts['cost_of_goods_sold'],
                'debit'       => $invoice->vehicle->total_cost,
                'credit'      => 0,
                'description' => "COGS: {$invoice->vehicle->stock_number}",
            ],
            // CR: Vehicle Inventory
            [
                'account_id'  => $accounts['vehicle_inventory'],
                'debit'       => 0,
                'credit'      => $invoice->vehicle->total_cost,
                'description' => "Inventory: {$invoice->vehicle->stock_number}",
            ],
        ];

        // Withholding tax entry
        if ($invoice->withholding_tax > 0) {
            $lines[] = [
                'account_id'  => $accounts['tax_payable'],
                'debit'       => 0,
                'credit'      => $invoice->withholding_tax,
                'description' => "WHT: {$invoice->invoice_number}",
            ];
            // Adjust the cash debit
            $lines[0]['debit'] = $invoice->net_amount;
        }

        return $this->postEntry([
            'branch_id'      => $branchId,
            'entry_date'     => $invoice->invoice_date,
            'narration'      => "Vehicle sale — Invoice {$invoice->invoice_number} — {$invoice->customer->full_name}",
            'reference_type' => 'sale_invoice',
            'reference_id'   => $invoice->id,
            'entry_type'     => 'sale',
            'is_auto'        => true,
        ], $lines);
    }

    // ── Auto-post on payment received ─────────────────────────────

    public function postPaymentEntry(Payment $payment): JournalEntry
    {
        $accounts = $this->getSystemAccounts();

        if ($payment->type === 'received') {
            $lines = [
                ['account_id' => $accounts['cash_in_hand'], 'debit' => $payment->amount,  'credit' => 0],
                ['account_id' => $accounts['accounts_receivable'], 'debit' => 0, 'credit' => $payment->amount],
            ];
        } else {
            $lines = [
                ['account_id' => $accounts['accounts_payable'], 'debit' => $payment->amount,  'credit' => 0],
                ['account_id' => $accounts['cash_in_hand'],     'debit' => 0,               'credit' => $payment->amount],
            ];
        }

        return $this->postEntry([
            'branch_id'      => $payment->branch_id,
            'entry_date'     => $payment->payment_date,
            'narration'      => "Payment {$payment->payment_number} — {$payment->type}",
            'reference_type' => 'payment',
            'reference_id'   => $payment->id,
            'entry_type'     => $payment->type === 'received' ? 'receipt' : 'payment',
            'is_auto'        => true,
        ], $lines);
    }

    // ── Auto-post on expense ───────────────────────────────────────

    public function postExpenseEntry(Expense $expense): JournalEntry
    {
        $accounts = $this->getSystemAccounts();
        $expenseAccountId = $expense->category->account_id ?? $accounts['general_expense'];

        return $this->postEntry([
            'branch_id'      => $expense->branch_id,
            'entry_date'     => $expense->expense_date,
            'narration'      => "Expense: {$expense->description} — {$expense->expense_number}",
            'reference_type' => 'expense',
            'reference_id'   => $expense->id,
            'entry_type'     => 'expense',
            'is_auto'        => true,
        ], [
            ['account_id' => $expenseAccountId,        'debit' => $expense->amount, 'credit' => 0],
            ['account_id' => $accounts['cash_in_hand'], 'debit' => 0, 'credit' => $expense->amount],
        ]);
    }

    // ── Trial Balance ──────────────────────────────────────────────

    public function getTrialBalance(?string $fromDate = null, ?string $toDate = null): array
    {
        $query = Account::with(['journalLines' => function ($q) use ($fromDate, $toDate) {
            if ($fromDate) $q->whereHas('journalEntry', fn($jq) => $jq->where('entry_date', '>=', $fromDate));
            if ($toDate)   $q->whereHas('journalEntry', fn($jq) => $jq->where('entry_date', '<=', $toDate));
        }])->active()->orderBy('account_code');

        return $query->get()->map(function (Account $account) {
            $debit  = $account->journalLines->sum('debit_amount');
            $credit = $account->journalLines->sum('credit_amount');
            return [
                'code'   => $account->account_code,
                'name'   => $account->account_name,
                'type'   => $account->account_type,
                'debit'  => $debit,
                'credit' => $credit,
            ];
        })->toArray();
    }

    // ── Profit & Loss ──────────────────────────────────────────────

    public function getProfitLoss(string $fromDate, string $toDate): array
    {
        $revenues = $this->sumAccountType('revenue', $fromDate, $toDate);
        $expenses = $this->sumAccountType('expense', $fromDate, $toDate);
        $cogs     = $this->sumSpecificAccount('cost_of_goods_sold', $fromDate, $toDate);

        $grossProfit = $revenues - $cogs;
        $netProfit   = $grossProfit - ($expenses - $cogs);

        return compact('revenues','cogs','grossProfit','expenses','netProfit');
    }

    // ── System accounts lookup ─────────────────────────────────────

    public function getSystemAccounts(): array
    {
        // Account codes are seeded by DefaultChartOfAccountsSeeder
        $codes = [
            'cash_in_hand'       => '1001',
            'accounts_receivable'=> '1100',
            'vehicle_inventory'  => '1200',
            'accounts_payable'   => '2001',
            'tax_payable'        => '2100',
            'sales_revenue'      => '4001',
            'cost_of_goods_sold' => '5001',
            'general_expense'    => '5100',
        ];

        $accounts = Account::whereIn('account_code', array_values($codes))->pluck('id','account_code');

        return array_map(fn($code) => $accounts[$code] ?? null, $codes);
    }

    private function sumAccountType(string $type, string $from, string $to): float
    {
        // Revenue, liability, and equity accounts have a credit-normal balance
        // (increase with credit). Asset and expense accounts have a debit-normal
        // balance (increase with debit). Using the wrong direction here silently
        // inverts the sign of the result.
        $creditNormal = in_array($type, ['revenue', 'liability', 'equity'], true);

        $expression = $creditNormal
            ? 'credit_amount - debit_amount'
            : 'debit_amount - credit_amount';

        return (float) JournalEntryLine::whereHas('account', fn($q) => $q->where('account_type', $type))
            ->whereHas('journalEntry', fn($q) => $q->whereBetween('entry_date', [$from, $to]))
            ->sum(\Illuminate\Support\Facades\DB::raw($expression));
    }

    private function sumSpecificAccount(string $key, string $from, string $to): float
    {
        $accounts = $this->getSystemAccounts();
        if (!$accounts[$key]) return 0;

        return (float) JournalEntryLine::where('account_id', $accounts[$key])
            ->whereHas('journalEntry', fn($q) => $q->whereBetween('entry_date', [$from, $to]))
            ->sum('debit_amount');
    }
}
