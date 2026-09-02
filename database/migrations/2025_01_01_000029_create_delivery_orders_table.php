<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('delivery_number', 30);
            $table->foreignId('sale_invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->date('delivery_date');
            $table->unsignedBigInteger('delivered_by');
            $table->text('condition_notes')->nullable();
            $table->json('accessories_list')->nullable();
            $table->string('customer_signature', 255)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->foreign('delivered_by')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unique(['tenant_id', 'delivery_number']);
            $table->index('sale_invoice_id');
        });
    }
    public function down(): void { Schema::dropIfExists('delivery_orders'); }
};
