<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('commission_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('name', 100);
            $table->enum('applies_to', ['salesman','manager','branch']);
            $table->enum('calc_type', ['fixed','percentage_profit','percentage_sale']);
            $table->decimal('value', 10, 4);
            $table->decimal('min_sale_price', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users');
            $table->index('tenant_id');
        });

        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->foreignId('sale_invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('commission_rule_id')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('referrer_name', 100)->nullable();
            $table->string('referrer_phone', 20)->nullable();
            $table->enum('commission_type', ['salesman','manager','referral']);
            $table->decimal('sale_amount', 12, 2);
            $table->decimal('profit_amount', 12, 2);
            $table->decimal('commission_amount', 12, 2);
            $table->enum('status', ['pending','approved','paid'])->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->unsignedBigInteger('journal_entry_id')->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('commission_rule_id')->references('id')->on('commission_rules')->nullOnDelete();
            $table->foreign('employee_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['tenant_id', 'sale_invoice_id']);
            $table->index(['tenant_id', 'employee_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('commissions');
        Schema::dropIfExists('commission_rules');
    }
};
