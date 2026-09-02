<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('booking_number', 30);
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('quotation_id')->nullable();
            $table->decimal('booking_amount', 12, 2);
            $table->decimal('agreed_sale_price', 12, 2);
            $table->date('expected_delivery_date')->nullable();
            $table->enum('payment_method', ['cash','bank_transfer','cheque','online'])->default('cash');
            $table->string('payment_reference', 100)->nullable();
            $table->enum('status', ['active','cancelled','converted'])->default('active');
            $table->string('cancellation_reason', 255)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->foreign('quotation_id')->references('id')->on('quotations')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users');
            $table->unique(['tenant_id', 'booking_number']);
            $table->index(['tenant_id', 'customer_id']);
            $table->index(['tenant_id', 'vehicle_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('bookings'); }
};
