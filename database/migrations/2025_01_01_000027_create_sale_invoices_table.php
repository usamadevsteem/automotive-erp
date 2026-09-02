<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('sale_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('invoice_number', 30);
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->decimal('sale_price', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('withholding_tax', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2);
            $table->enum('payment_type', ['cash','installment','bank_transfer','cheque'])->default('cash');
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('balance_due', 12, 2)->default(0);
            $table->date('invoice_date');
            $table->enum('status', ['draft','issued','paid','partial','cancelled'])->default('draft');
            $table->string('cancelled_reason', 255)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->foreign('booking_id')->references('id')->on('bookings')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users');
            $table->unique(['tenant_id', 'invoice_number']);
            $table->index(['tenant_id', 'customer_id']);
            $table->index(['tenant_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('sale_invoices'); }
};
