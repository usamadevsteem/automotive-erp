<?php

namespace Database\Seeders;

use App\Models\Platform\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'          => 'Starter',
                'slug'          => 'starter',
                'price_monthly' => 4999.00,
                'price_annual'  => 47990.00,
                'max_vehicles'  => 50,
                'max_users'     => 3,
                'max_branches'  => 1,
                'features'      => ['qr_codes' => true, 'whatsapp_crm' => false, 'excel_import' => true],
                'is_active'     => true,
            ],
            [
                'name'          => 'Growth',
                'slug'          => 'growth',
                'price_monthly' => 12999.00,
                'price_annual'  => 124790.00,
                'max_vehicles'  => 200,
                'max_users'     => 10,
                'max_branches'  => 3,
                'features'      => ['qr_codes' => true, 'whatsapp_crm' => true, 'excel_import' => true],
                'is_active'     => true,
            ],
            [
                'name'          => 'Professional',
                'slug'          => 'professional',
                'price_monthly' => 29999.00,
                'price_annual'  => 287990.00,
                'max_vehicles'  => 1000,
                'max_users'     => 25,
                'max_branches'  => 10,
                'features'      => ['qr_codes' => true, 'whatsapp_crm' => true, 'excel_import' => true],
                'is_active'     => true,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }

        $this->command->info('Plans seeded.');
    }
}
