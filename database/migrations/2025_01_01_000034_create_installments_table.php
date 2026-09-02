<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('installment_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('plan_number', 30);
            $table->foreignId('sale_invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_sale_price', 12, 2);
            $table->decimal('down_payment', 12, 2);
            $table->decimal('financed_amount', 12, 2);
            $table->tinyInteger('installment_count')->unsigned();
            $table->decimal('installment_amount', 12, 2);
            $table->enum('frequency', ['monthly','bi_monthly','quarterly'])->default('monthly');
            $table->date('first_due_date');
            $table->decimal('late_fee_per_day', 8, 2)->default(0);
            $table->string('guarantor_name', 100)->nullable();
            $table->string('guarantor_cnic', 20)->nullable();
            $table->string('guarantor_phone', 20)->nullable();
            $table->enum('status', ['active','completed','defaulted'])->default('active');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users');
            $table->unique(['tenant_id', 'plan_number']);
            $table->index(['tenant_id', 'customer_id']);
        });

        Schema::create('installment_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->foreignId('plan_id')->constrained('installment_plans')->cascadeOnDelete();
            $table->tinyInteger('installment_number')->unsigned();
            $table->date('due_date');
            $table->decimal('amount_due', 12, 2);
            $table->decimal('late_fee', 12, 2)->default(0);
            $table->decimal('total_due', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->date('paid_date')->nullable();
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->enum('status', ['pending','paid','partial','overdue'])->default('pending');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index('plan_id');
            $table->index(['tenant_id', 'due_date', 'status']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('installment_schedules');
        Schema::dropIfExists('installment_plans');
    }
};
