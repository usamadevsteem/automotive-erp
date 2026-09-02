<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('payment_number', 30);
            $table->enum('type', ['received','paid']);
            $table->enum('party_type', ['customer','vendor','employee','other']);
            $table->unsignedBigInteger('party_id');
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['cash','cheque','bank_transfer','online'])->default('cash');
            $table->string('cheque_number', 50)->nullable();
            $table->date('cheque_date')->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('reference_number', 100)->nullable();
            $table->date('payment_date');
            $table->unsignedBigInteger('journal_entry_id')->nullable();
            $table->string('notes', 255)->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unique(['tenant_id', 'payment_number']);
            $table->index(['tenant_id', 'party_type', 'party_id']);
            $table->index(['reference_type', 'reference_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('payments'); }
};
