<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_status_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->string('reason', 255)->nullable();
            $table->unsignedBigInteger('changed_by');

            // Immutable audit — no updated_at, no soft delete
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('changed_by')->references('id')->on('users');

            $table->index('vehicle_id');
            $table->index(['tenant_id', 'created_at'], 'idx_tenant_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_status_logs');
    }
};
