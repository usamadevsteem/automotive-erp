<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class DefaultExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $tenant = app('tenant');

        $map = [
            'Salaries'       => '5101',
            'Rent'           => '5102',
            'Utilities'      => '5103',
            'Marketing'      => '5104',
            'Fuel'           => '5100',
            'Workshop'       => '5100',
            'Transportation' => '5100',
            'Miscellaneous'  => '5100',
        ];

        foreach ($map as $name => $accountCode) {
            $account = Account::where('tenant_id', $tenant->id)
                ->where('account_code', $accountCode)->first();

            ExpenseCategory::updateOrCreate(
                ['tenant_id' => $tenant->id, 'name' => $name],
                ['account_id' => $account?->id, 'is_active' => true]
            );
        }
    }
}
