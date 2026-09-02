<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('plans');
            $table->enum('billing_cycle', ['monthly','annual'])->default('monthly');
            $table->decimal('amount', 10, 2);
            $table->timestamp('started_at');
            $table->timestamp('expires_at');
            $table->enum('status', ['active','cancelled','expired'])->default('active');
            $table->string('payment_ref', 100)->nullable();
            $table->timestamps();
            $table->index('tenant_id');
            $table->index('expires_at');
        });
    }
    public function down(): void { Schema::dropIfExists('subscriptions'); }
};
