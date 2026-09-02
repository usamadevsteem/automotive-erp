<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

/**
 * Run per-tenant after tenant creation (called from TenantService),
 * OR run manually for the demo tenant during initial setup.
 */
class DefaultChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            // Assets
            ['1001', 'Cash in Hand',         'asset',     'cash'],
            ['1002', 'Bank Account',         'asset',     'bank'],
            ['1100', 'Accounts Receivable',  'asset',     'receivable'],
            ['1200', 'Vehicle Inventory',    'asset',     'inventory'],
            ['1300', 'Office Equipment',     'asset',     'fixed_asset'],

            // Liabilities
            ['2001', 'Accounts Payable',     'liability', 'payable'],
            ['2100', 'Tax Payable',          'liability', 'tax'],
            ['2200', 'Loans Payable',        'liability', 'loan'],

            // Equity
            ['3001', 'Owner Equity',         'equity',    null],
            ['3002', 'Retained Earnings',    'equity',    null],

            // Revenue
            ['4001', 'Sales Revenue',        'revenue',   'vehicle_sales'],
            ['4002', 'Commission Income',    'revenue',   null],

            // Expenses
            ['5001', 'Cost of Goods Sold',   'expense',   'cogs'],
            ['5100', 'General Expense',      'expense',   null],
            ['5101', 'Salaries Expense',     'expense',   null],
            ['5102', 'Rent Expense',         'expense',   null],
            ['5103', 'Utilities Expense',    'expense',   null],
            ['5104', 'Marketing Expense',    'expense',   null],
            ['5105', 'Commission Expense',   'expense',   null],
        ];

        foreach ($accounts as [$code, $name, $type, $subtype]) {
            Account::updateOrCreate(
                ['tenant_id' => app('tenant')->id, 'account_code' => $code],
                [
                    'account_name'    => $name,
                    'account_type'    => $type,
                    'account_subtype' => $subtype,
                    'is_system'       => true,
                    'is_active'       => true,
                ]
            );
        }
    }
}
