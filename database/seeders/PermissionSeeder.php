<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view-dashboard',
            // Branches
            'manage-branches',
            // Users
            'manage-users',
            // Roles
            'manage-roles',
            // Vehicles
            'view-vehicles', 'create-vehicles', 'edit-vehicles',
            'delete-vehicles', 'view-vehicle-cost', 'transfer-vehicles',
            // Customers
            'view-customers', 'create-customers', 'edit-customers', 'delete-customers',
            // Leads
            'view-leads', 'create-leads', 'edit-leads', 'assign-leads', 'delete-leads',
            // Sales
            'view-quotations', 'create-quotations', 'edit-quotations',
            'view-bookings', 'create-bookings', 'edit-bookings',
            'view-invoices', 'create-invoices', 'edit-invoices', 'cancel-invoices',
            'view-deliveries', 'create-deliveries',
            // Trade-in
            'view-trade-ins', 'create-trade-ins', 'approve-trade-ins',
            // Accounting
            'view-accounts', 'manage-accounts',
            'view-journal-entries', 'create-journal-entries',
            'view-payments', 'create-payments',
            'view-expenses', 'create-expenses', 'approve-expenses',
            'view-vendors', 'manage-vendors',
            'view-financial-reports',
            // Installments
            'view-installments', 'create-installments', 'collect-installments',
            // Commissions
            'view-commissions', 'manage-commission-rules', 'approve-commissions', 'pay-commissions',
            // Documents
            'generate-documents', 'void-documents', 'view-document-history', 'manage-document-templates',
            // WhatsApp
            'view-whatsapp', 'reply-whatsapp', 'manage-whatsapp-settings',
            // Reports
            'view-inventory-reports', 'view-sales-reports', 'view-crm-reports',
            'view-accounting-reports', 'view-installment-reports', 'view-commission-reports',
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $this->command->info(count($permissions) . ' permissions seeded.');
    }
}
