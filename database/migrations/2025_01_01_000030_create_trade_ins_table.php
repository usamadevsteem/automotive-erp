<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('trade_ins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('branch_id');
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('new_vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->unsignedBigInteger('sale_invoice_id')->nullable();
            $table->string('trade_make', 100);
            $table->string('trade_model', 100);
            $table->year('trade_year');
            $table->string('trade_registration', 30)->nullable();
            $table->unsignedInteger('trade_mileage')->nullable();
            $table->enum('trade_condition', ['excellent','good','fair','poor'])->default('good');
            $table->string('trade_color', 50)->nullable();
            $table->string('chassis_number', 50)->nullable();
            $table->string('engine_number', 50)->nullable();
            $table->decimal('market_value', 12, 2)->default(0);
            $table->decimal('offered_value', 12, 2)->default(0);
            $table->decimal('approved_value', 12, 2)->nullable();
            $table->decimal('difference_amount', 12, 2)->nullable();
            $table->enum('status', ['pending','approved','rejected','completed'])->default('pending');
            $table->unsignedBigInteger('evaluated_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->foreign('sale_invoice_id')->references('id')->on('sale_invoices')->nullOnDelete();
            $table->foreign('evaluated_by')->references('id')->on('users');
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['tenant_id', 'customer_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('trade_ins'); }
};
