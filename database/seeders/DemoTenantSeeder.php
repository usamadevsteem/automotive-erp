<?php

namespace Database\Seeders;

use App\Models\Platform\Tenant;
use App\Services\TenantService;
use Illuminate\Database\Seeder;

class DemoTenantSeeder extends Seeder
{
    public function run(): void
    {
        $subdomain = env('DEMO_TENANT_SUBDOMAIN', 'demo');
        $email = env('DEMO_ADMIN_EMAIL', 'admin@demo.com');
        $password = env('DEMO_ADMIN_PASSWORD');

        if (Tenant::where('subdomain', $subdomain)->exists()) {
            $this->command->info('Demo tenant already exists.');
            return;
        }

        if (!$password) {
            $this->command->warn('DEMO_ADMIN_PASSWORD is not set; demo tenant was not created.');
            return;
        }

        app(TenantService::class)->create([
            'subdomain'    => $subdomain,
            'company_name' => env('DEMO_COMPANY_NAME', 'Demo Motors'),
            'owner_name'   => env('DEMO_OWNER_NAME', 'Admin'),
            'email'        => $email,
            'phone'        => env('DEMO_PHONE', '03001234567'),
            'city'         => env('DEMO_CITY', 'Lahore'),
            'password'     => $password,
            'plan_slug'    => env('DEMO_PLAN_SLUG', 'professional'),
        ]);

        $this->command->info('Demo tenant and admin user created.');
    }
}
