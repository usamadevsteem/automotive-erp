<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('vehicle_qr_scans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->string('qr_code', 50);
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('scanned_at')->useCurrent();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index('vehicle_id');
            $table->index('qr_code');
            $table->index(['tenant_id', 'scanned_at'], 'idx_qrscans_tenant_date');
        });
    }
    public function down(): void { Schema::dropIfExists('vehicle_qr_scans'); }
};
