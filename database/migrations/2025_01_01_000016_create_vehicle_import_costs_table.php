<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_import_costs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->foreignId('vehicle_id')->unique()->constrained('vehicles')->cascadeOnDelete();

            // ── Individual Cost Lines ─────────────────────────────────
            $table->decimal('auction_price', 12, 2)->default(0);
            $table->decimal('auction_charges', 12, 2)->default(0);
            $table->decimal('shipping_charges', 12, 2)->default(0);
            $table->decimal('clearing_charges', 12, 2)->default(0);
            $table->decimal('customs_duty', 12, 2)->default(0);
            $table->decimal('port_charges', 12, 2)->default(0);
            $table->decimal('registration_charges', 12, 2)->default(0);
            $table->decimal('transportation_charges', 12, 2)->default(0);
            $table->decimal('other_charges', 12, 2)->default(0);
            // Total synced back to vehicles.landing_cost by ImportCostService
            $table->decimal('total_import_cost', 12, 2)->default(0)->comment('Sum of all above; synced to vehicles.landing_cost');

            $table->text('notes')->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();

            $table->index('tenant_id');
            $table->index('vehicle_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_import_costs');
    }
};
