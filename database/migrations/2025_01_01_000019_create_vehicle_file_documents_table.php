<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('vehicle_file_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->enum('document_type', [
                'registration_book','smart_card','transfer_letter',
                'open_transfer_letter','biometric_slip','auction_sheet',
                'import_bill','customs_clearance','insurance',
                'token_tax','sale_agreement','affidavit','noc','other',
            ]);
            $table->string('document_label', 100)->nullable();
            $table->string('file_path', 500);
            $table->string('file_name', 255)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->date('expiry_date')->nullable();
            $table->boolean('is_original')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->string('notes', 255)->nullable();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamp('uploaded_at')->useCurrent();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['tenant_id', 'vehicle_id'], 'idx_filedocs_tenant_vehicle');
            $table->index(['tenant_id', 'document_type'], 'idx_tenant_doctype');
            $table->index('expiry_date');
        });
    }
    public function down(): void { Schema::dropIfExists('vehicle_file_documents'); }
};
