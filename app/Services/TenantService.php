<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class TenantService
{
    public function create(array $data): Tenant
    {
        return DB::transaction(function () use ($data) {
            $plan = Plan::where('slug', $data['plan_slug'] ?? 'starter')->firstOrFail();

            $tenant = Tenant::create([
                'subdomain'      => strtolower($data['subdomain']),
                'company_name'   => $data['company_name'],
                'owner_name'     => $data['owner_name'],
                'email'          => $data['email'],
                'phone'          => $data['phone'],
                'address'        => $data['address'] ?? null,
                'city'           => $data['city'] ?? null,
                'plan_id'        => $plan->id,
                'trial_ends_at'  => now()->addDays(14),
                'plan_expires_at'=> now()->addDays(14),
                'status'         => 'trial',
                'settings'       => [
                    'timezone'    => 'Asia/Karachi',
                    'currency'    => 'PKR',
                    'date_format' => 'd/m/Y',
                ],
            ]);

            app()->instance('tenant', $tenant);
            setPermissionsTeamId($tenant->id);

            $branch = Branch::create([
                'tenant_id' => $tenant->id,
                'name'      => $data['company_name'] . ' - Main',
                'code'      => 'MAIN',
                'city'      => $data['city'] ?? null,
                'phone'     => $data['phone'],
                'email'     => $data['email'],
                'is_main'   => true,
                'is_active' => true,
            ]);

            $roles = $this->seedDefaultRoles($tenant->id);

            $user = User::create([
                'tenant_id'   => $tenant->id,
                'branch_id'   => $branch->id,
                'name'        => $data['owner_name'],
                'email'       => $data['email'],
                'phone'       => $data['phone'],
                'password'    => Hash::make($data['password']),
                'designation' => 'Owner',
                'is_active'   => true,
            ]);

            $user->assignRole($roles['dealer_owner']);

            // Seed default chart of accounts, expense categories, and document templates
            (new \Database\Seeders\DefaultChartOfAccountsSeeder())->run();
            (new \Database\Seeders\DefaultExpenseCategorySeeder())->run();
            (new \Database\Seeders\DocumentTemplateSeeder())->run();

            Subscription::create([
                'tenant_id'     => $tenant->id,
                'plan_id'       => $plan->id,
                'billing_cycle' => 'monthly',
                'amount'        => 0,
                'started_at'    => now(),
                'expires_at'    => now()->addDays(14),
                'status'        => 'active',
            ]);

            return $tenant;
        });
    }

    private function seedDefaultRoles(int $tenantId): array
    {
        $allPermissions = Permission::all()->pluck('name')->toArray();
        $definitions    = $this->getRolePermissions();
        $created        = [];

        foreach ($definitions as $name => $perms) {
            $role = Role::create(['name' => $name, 'guard_name' => 'web', 'team_id' => $tenantId]);
            $role->syncPermissions($perms === 'all' ? $allPermissions : $perms);
            $created[$this->toKey($name)] = $role;
        }

        return $created;
    }

    private function getRolePermissions(): array
    {
        return [
            'Dealer Owner'   => 'all',
            'General Manager'=> 'all',
            'Branch Manager' => [
                'view-dashboard','view-vehicles','create-vehicles','edit-vehicles',
                'view-vehicle-cost','transfer-vehicles','view-customers','create-customers',
                'edit-customers','view-leads','create-leads','edit-leads','assign-leads',
                'view-quotations','create-quotations','view-bookings','create-bookings',
                'view-invoices','create-invoices','view-deliveries','create-deliveries',
                'view-commissions','approve-commissions','generate-documents',
                'view-document-history','view-whatsapp','reply-whatsapp',
                'view-inventory-reports','view-sales-reports','view-crm-reports',
                'view-expenses','create-expenses','approve-expenses',
                'view-payments','create-payments',
            ],
            'Sales Manager'  => [
                'view-dashboard','view-vehicles','create-vehicles','edit-vehicles',
                'view-vehicle-cost','view-customers','create-customers','edit-customers',
                'view-leads','create-leads','edit-leads','assign-leads',
                'view-quotations','create-quotations','edit-quotations',
                'view-bookings','create-bookings','view-invoices','create-invoices',
                'view-deliveries','create-deliveries',
                'view-commissions','generate-documents','view-document-history',
                'view-whatsapp','reply-whatsapp','view-sales-reports','view-crm-reports',
                'view-inventory-reports',
            ],
            'Sales Executive'=> [
                'view-dashboard','view-vehicles','view-customers','create-customers',
                'edit-customers','view-leads','create-leads','edit-leads',
                'view-quotations','create-quotations','view-bookings','create-bookings',
                'view-invoices','view-deliveries','generate-documents',
                'view-whatsapp','reply-whatsapp',
            ],
            'Inventory Manager'=> [
                'view-dashboard','view-vehicles','create-vehicles','edit-vehicles',
                'view-vehicle-cost','transfer-vehicles','delete-vehicles',
                'view-inventory-reports',
            ],
            'Accountant'     => [
                'view-dashboard','view-accounts','manage-accounts',
                'view-journal-entries','create-journal-entries',
                'view-payments','create-payments','view-expenses','create-expenses',
                'approve-expenses','view-vendors','manage-vendors',
                'view-commissions','pay-commissions','view-financial-reports',
                'view-accounting-reports',
                'view-commission-reports','view-invoices','view-vehicle-cost',
                'generate-documents','view-document-history',
            ],
            'Customer Support'=> [
                'view-dashboard','view-customers','view-leads','create-leads',
                'view-whatsapp','reply-whatsapp','view-quotations',
                'view-invoices','generate-documents',
            ],
            'Data Entry Operator'=> [
                'view-dashboard','view-vehicles','create-vehicles','edit-vehicles',
                'view-customers','create-customers','edit-customers',
                'view-leads','create-leads',
            ],
            'HR Manager'     => [
                'view-dashboard','view-commissions','manage-commission-rules',
                'approve-commissions','pay-commissions','view-commission-reports',
            ],
        ];
    }

    private function toKey(string $name): string
    {
        return str_replace([' ', '-'], '_', strtolower($name));
    }
}
