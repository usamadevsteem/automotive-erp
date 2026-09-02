<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('branch_id');

            // ── Auto-generated stock number ───────────────────────────
            $table->string('stock_number', 30);

            // ── Classification ────────────────────────────────────────
            $table->foreignId('make_id')->constrained('vehicle_makes');
            $table->foreignId('model_id')->constrained('vehicle_models');
            $table->foreignId('variant_id')->nullable()->constrained('vehicle_variants')->nullOnDelete();
            $table->enum('category', [
                'local_car',
                'imported_car',
                'suv',
                'pickup',
                'hybrid',
                'electric',
            ])->default('local_car');

            // ── Physical Details ──────────────────────────────────────
            $table->year('year');
            $table->year('registration_year')->nullable();
            $table->string('color', 50)->nullable();
            $table->unsignedInteger('mileage')->default(0);       // in km
            $table->enum('fuel_type', ['petrol', 'diesel', 'hybrid', 'electric', 'cng'])->default('petrol');
            $table->enum('transmission', ['manual', 'automatic', 'cvt'])->default('automatic');
            $table->string('engine_capacity', 20)->nullable();    // '1800cc'
            $table->enum('condition_grade', ['excellent', 'good', 'fair', 'poor'])->default('good');

            // ── Identity Numbers ──────────────────────────────────────
            $table->string('registration_number', 30)->nullable();
            $table->string('chassis_number', 50)->nullable();
            $table->string('engine_number', 50)->nullable();
            $table->string('vin_number', 20)->nullable();

            // ── Import Specific ───────────────────────────────────────
            $table->enum('import_status', ['local', 'imported', 'auction'])->default('local');
            $table->string('auction_grade', 10)->nullable();      // 3.5, 4, 4.5, S

            // ── Costing (PKR) ─────────────────────────────────────────
            $table->decimal('purchase_price', 12, 2)->default(0);
            $table->decimal('landing_cost', 12, 2)->default(0);   // sum synced from import costs
            $table->decimal('repair_cost', 12, 2)->default(0);
            $table->decimal('misc_cost', 12, 2)->default(0);
            // total_cost is computed in app layer (MySQL generated cols have limitations with updates)
            $table->decimal('total_cost', 12, 2)->default(0)->comment('Maintained by VehicleService on save');

            // ── Pricing ───────────────────────────────────────────────
            $table->decimal('sale_price', 12, 2)->default(0);
            $table->decimal('min_sale_price', 12, 2)->default(0);
            $table->decimal('expected_profit', 12, 2)->default(0)->comment('sale_price - total_cost');
            $table->decimal('actual_profit', 12, 2)->nullable()->comment('Set at time of sale');

            // ── Status & Workflow ─────────────────────────────────────
            $table->enum('status', [
                'available',
                'reserved',
                'sold',
                'delivered',
                'pending_inspection',
            ])->default('pending_inspection');

            // ── QR Code ───────────────────────────────────────────────
            $table->string('qr_code', 50)->nullable()->unique()->comment('UUID for public QR page');
            $table->string('qr_image_path', 255)->nullable();

            // ── Tracking ──────────────────────────────────────────────
            $table->unsignedBigInteger('added_by');
            $table->unsignedBigInteger('sold_by')->nullable();
            $table->timestamp('sold_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ── Constraints ───────────────────────────────────────────
            $table->unique(['tenant_id', 'stock_number']);

            // ── Foreign Keys ──────────────────────────────────────────
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->foreign('added_by')->references('id')->on('users');
            $table->foreign('sold_by')->references('id')->on('users')->nullOnDelete();

            // ── Indexes (performance critical) ────────────────────────
            $table->index(['tenant_id', 'status'],             'idx_tenant_status');
            $table->index(['tenant_id', 'branch_id'],          'idx_tenant_branch');
            $table->index(['tenant_id', 'make_id', 'model_id'],'idx_tenant_make_model');
            $table->index(['tenant_id', 'category'],           'idx_tenant_category');
            $table->index(['tenant_id', 'created_at'],         'idx_tenant_created');
            $table->index('chassis_number');
            $table->index('registration_number');
            $table->index('qr_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
