<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Platform\Tenant;
use App\Models\Quotation;
use App\Models\SaleInvoice;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleMake;
use App\Models\VehicleModel;
use App\Models\VehicleVariant;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    private array $pakistaniNames = [
        'Ahmed Raza','Bilal Hussain','Farhan Malik','Hassan Sheikh','Imran Qureshi',
        'Kamran Butt','Nadeem Chaudhry','Owais Farooq','Sami Ullah','Tariq Mehmood',
        'Usman Ghani','Waqas Anjum','Zeeshan Iqbal','Adnan Siddiqui','Faisal Rashid',
        'Asad Javed','Danish Ali','Hamza Yousuf','Junaid Akram','Kashif Naveed',
    ];

    private array $cities = ['Lahore','Karachi','Islamabad','Faisalabad','Rawalpindi','Multan','Gujranwala'];

    private array $colors = ['White','Black','Silver','Grey','Red','Blue','Bronze'];

    public function run(): void
    {
        $tenant = Tenant::where('subdomain', 'demo')->first();

        if (!$tenant) {
            $this->command->error('No tenant with subdomain "demo" found. Create it first (see TenantService::create).');
            return;
        }

        app()->instance('tenant', $tenant);
        setPermissionsTeamId($tenant->id);

        $admin = User::where('tenant_id', $tenant->id)->where('is_active', true)->first();

        if (!$admin) {
            $this->command->error('No active user found for this tenant to attribute records to.');
            return;
        }

        Auth::login($admin);

        $branch = Branch::first();
        if (!$branch) {
            $this->command->error('No branch found for this tenant.');
            return;
        }

        $makes = VehicleMake::with('models.variants')->get();
        if ($makes->isEmpty()) {
            $this->command->error('No vehicle makes found. Run VehicleMakeSeeder first.');
            return;
        }

        $this->command->info('Seeding demo data for tenant: ' . $tenant->company_name);

        // ── Customers ────────────────────────────────────────────────
        $customers = collect();
        foreach (range(1, 12) as $i) {
            $name = $this->pakistaniNames[array_rand($this->pakistaniNames)] . ' ' . $i;
            $customers->push(Customer::create([
                'branch_id'      => $branch->id,
                'full_name'      => $name,
                'mobile'         => '03' . rand(0, 4) . rand(1000000, 9999999),
                'email'          => Str::slug($name) . '@example.com',
                'city'           => $this->cities[array_rand($this->cities)],
                'customer_type'  => ['buyer','buyer','buyer','seller','both'][array_rand([0,1,2,3,4])],
                'source'         => array_rand(Customer::SOURCES),
                'tax_status'     => array_rand(['filer'=>1,'non_filer'=>1,'unknown'=>1]),
                'assigned_to'    => $admin->id,
                'created_by'     => $admin->id,
            ]));
        }
        $this->command->info("  Created {$customers->count()} customers.");

        // ── Leads ────────────────────────────────────────────────────
        $leadStatuses = ['new','new','contacted','contacted','qualified','negotiation','won','lost'];
        $leadCount = 0;
        foreach (range(1, 15) as $i) {
            $linkToCustomer = $i <= 6;
            $customer = $linkToCustomer ? $customers->random() : null;
            $make = $makes->random();
            $model = $make->models->isNotEmpty() ? $make->models->random() : null;

            Lead::create([
                'branch_id'        => $branch->id,
                'customer_id'      => $customer?->id,
                'full_name'        => $customer?->full_name ?? ($this->pakistaniNames[array_rand($this->pakistaniNames)] . ' ' . $i),
                'phone'            => '03' . rand(0, 4) . rand(1000000, 9999999),
                'email'            => null,
                'source'           => array_rand(\App\Models\Lead::SOURCES),
                'vehicle_interest' => trim($make->name . ' ' . ($model?->name ?? '')),
                'budget'           => rand(15, 90) * 100000,
                'status'           => $leadStatuses[array_rand($leadStatuses)],
                'assigned_to'      => $admin->id,
                'next_follow_up'   => now()->addDays(rand(-3, 14)),
                'created_by'       => $admin->id,
            ]);
            $leadCount++;
        }
        $this->command->info("  Created {$leadCount} leads.");

        // ── Vehicles ─────────────────────────────────────────────────
        $vehicleStatuses = ['available','available','available','reserved','pending_inspection','sold','delivered'];
        $vehicles = collect();

        foreach (range(1, 18) as $i) {
            $make = $makes->random();
            $model = $make->models->isNotEmpty() ? $make->models->random() : null;
            $variant = $model && $model->variants->isNotEmpty() ? $model->variants->random() : null;

            if (!$model) continue;

            $purchase = rand(15, 80) * 100000;
            $repair   = rand(0, 5) * 10000;
            $misc     = rand(0, 2) * 10000;
            $landing  = 0;
            $totalCost = $purchase + $repair + $misc + $landing;
            $sale     = (int) round($totalCost * (1 + (rand(5, 18) / 100)), -3);
            $status   = $vehicleStatuses[array_rand($vehicleStatuses)];

            $vehicle = Vehicle::create([
                'branch_id'        => $branch->id,
                'stock_number'     => 'STK-' . now()->year . '-' . str_pad((string) (100 + $i), 4, '0', STR_PAD_LEFT),
                'make_id'          => $make->id,
                'model_id'         => $model->id,
                'variant_id'       => $variant?->id,
                'category'         => 'local_car',
                'year'             => rand(2018, 2026),
                'color'            => $this->colors[array_rand($this->colors)],
                'mileage'          => rand(0, 120000),
                'fuel_type'        => ['petrol','petrol','diesel','hybrid'][array_rand([0,1,2,3])],
                'transmission'     => ['automatic','manual','cvt'][array_rand([0,1,2])],
                'engine_capacity'  => ['1000cc','1300cc','1500cc','1800cc','2000cc'][array_rand([0,1,2,3,4])],
                'condition_grade'  => ['excellent','good','fair'][array_rand([0,1,2])],
                'import_status'    => 'local',
                'purchase_price'   => $purchase,
                'landing_cost'     => $landing,
                'repair_cost'      => $repair,
                'misc_cost'        => $misc,
                'total_cost'       => $totalCost,
                'sale_price'       => $sale,
                'min_sale_price'   => $totalCost,
                'expected_profit'  => $sale - $totalCost,
                'status'           => $status,
                'added_by'         => $admin->id,
                'sold_by'          => in_array($status, ['sold','delivered']) ? $admin->id : null,
                'sold_at'          => in_array($status, ['sold','delivered']) ? now()->subDays(rand(1, 25)) : null,
            ]);

            $vehicles->push($vehicle);
        }
        $this->command->info("  Created {$vehicles->count()} vehicles.");

        // ── Quotations ───────────────────────────────────────────────
        $availableVehicles = $vehicles->whereIn('status', ['available','reserved'])->values();
        $quotations = collect();
        $qCount = min(8, $availableVehicles->count());

        foreach (range(1, $qCount) as $i) {
            $vehicle = $availableVehicles[$i - 1];
            $customer = $customers->random();
            $discount = rand(0, 3) * 10000;

            $quotations->push(Quotation::create([
                'branch_id'        => $branch->id,
                'quotation_number' => 'QUO-' . now()->year . '-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'customer_id'      => $customer->id,
                'vehicle_id'       => $vehicle->id,
                'sale_price'       => $vehicle->sale_price,
                'discount'         => $discount,
                'net_price'        => $vehicle->sale_price - $discount,
                'valid_until'      => now()->addDays(rand(3, 14)),
                'status'           => ['draft','sent','sent','accepted','rejected'][array_rand([0,1,2,3,4])],
                'created_by'       => $admin->id,
            ]));
        }
        $this->command->info("  Created {$quotations->count()} quotations.");

        // ── Bookings ─────────────────────────────────────────────────
        $reservedVehicles = $vehicles->where('status', 'reserved')->values();
        $bookings = collect();

        foreach ($reservedVehicles as $i => $vehicle) {
            $customer = $customers->random();
            $bookings->push(Booking::create([
                'branch_id'               => $branch->id,
                'booking_number'          => 'BKG-' . now()->year . '-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'customer_id'             => $customer->id,
                'vehicle_id'              => $vehicle->id,
                'booking_amount'          => rand(5, 15) * 10000,
                'agreed_sale_price'       => $vehicle->sale_price,
                'expected_delivery_date'  => now()->addDays(rand(5, 20)),
                'payment_method'          => 'bank_transfer',
                'status'                  => 'active',
                'created_by'              => $admin->id,
            ]));
        }
        $this->command->info("  Created {$bookings->count()} bookings.");

        // ── Sale Invoices + Payments ─────────────────────────────────
        $soldVehicles = $vehicles->whereIn('status', ['sold','delivered'])->values();
        $invoiceCount = 0;
        $paymentCount = 0;

        foreach ($soldVehicles as $i => $vehicle) {
            $customer = $customers->random();
            $discount = rand(0, 2) * 10000;
            $net = $vehicle->sale_price - $discount;
            $isPaid = rand(0, 1) === 1;
            $amountPaid = $isPaid ? $net : (int) round($net * 0.5, -3);

            $invoice = SaleInvoice::create([
                'branch_id'       => $branch->id,
                'invoice_number'  => 'INV-' . now()->year . '-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'customer_id'     => $customer->id,
                'vehicle_id'      => $vehicle->id,
                'sale_price'      => $vehicle->sale_price,
                'discount'        => $discount,
                'net_amount'      => $net,
                'payment_type'    => $isPaid ? 'cash' : 'installment',
                'amount_paid'     => $amountPaid,
                'balance_due'     => $net - $amountPaid,
                'invoice_date'    => $vehicle->sold_at ?? now(),
                'status'          => $isPaid ? 'paid' : 'partial',
                'created_by'      => $admin->id,
            ]);
            $invoiceCount++;

            Payment::create([
                'branch_id'       => $branch->id,
                'payment_number'  => 'PAY-' . now()->year . '-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'type'            => 'received',
                'party_type'      => 'customer',
                'party_id'        => $customer->id,
                'reference_type'  => 'sale_invoice',
                'reference_id'    => $invoice->id,
                'amount'          => $amountPaid,
                'payment_method'  => ['cash','bank_transfer','cheque'][array_rand([0,1,2])],
                'payment_date'    => $invoice->invoice_date,
                'created_by'      => $admin->id,
            ]);
            $paymentCount++;
        }
        $this->command->info("  Created {$invoiceCount} sale invoices and {$paymentCount} payments.");

        // ── Vendors & Expenses ───────────────────────────────────────
        $vendors = collect();
        foreach (['Al-Karam Auto Parts','Speedy Detailing Services','City Towing Co.'] as $vendorName) {
            $vendors->push(Vendor::create([
                'name'        => $vendorName,
                'vendor_type' => ['parts_vendor','service_vendor','service_vendor'][array_rand([0,1,2])],
                'phone'       => '021' . rand(10000000, 99999999),
                'city'        => $this->cities[array_rand($this->cities)],
                'is_active'   => true,
            ]));
        }

        $category = ExpenseCategory::first() ?? ExpenseCategory::create([
            'name'      => 'General',
            'is_active' => true,
        ]);

        $expenseDescriptions = [
            'Showroom electricity bill','Vehicle detailing service','Staff salaries',
            'Office rent','Internet & phone bill','Vehicle repair parts',
            'Marketing & advertising','Fuel for test drives','Office supplies',
        ];

        $expenseCount = 0;
        foreach (range(1, 10) as $i) {
            Expense::create([
                'branch_id'        => $branch->id,
                'expense_number'   => 'EXP-' . now()->year . '-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'category_id'      => $category->id,
                'description'      => $expenseDescriptions[array_rand($expenseDescriptions)],
                'amount'           => rand(5, 80) * 1000,
                'payment_method'   => ['cash','bank_transfer'][array_rand([0,1])],
                'vendor_id'        => rand(0, 1) ? $vendors->random()->id : null,
                'expense_date'     => now()->subDays(rand(0, 25)),
                'status'           => 'approved',
                'approved_by'      => $admin->id,
                'created_by'       => $admin->id,
            ]);
            $expenseCount++;
        }
        $this->command->info("  Created {$vendors->count()} vendors and {$expenseCount} expenses.");

        $this->command->info('Demo data seeding complete.');
    }
}
