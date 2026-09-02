<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('customer_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('lead_id')->nullable();
            $table->enum('type', ['call','meeting','whatsapp','email','note','task']);
            $table->string('subject', 200)->nullable();
            $table->text('description')->nullable();
            $table->string('outcome', 255)->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users');
            $table->index(['tenant_id', 'customer_id']);
            $table->index(['tenant_id', 'scheduled_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('customer_activities'); }
};
