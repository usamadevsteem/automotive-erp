<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('customer_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->enum('document_type', ['cnic_front','cnic_back','passport','utility_bill','salary_slip','other']);
            $table->string('file_path', 500);
            $table->date('expiry_date')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->unsignedBigInteger('uploaded_by');
            $table->timestamp('uploaded_at')->useCurrent();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('uploaded_by')->references('id')->on('users');
            $table->index(['tenant_id', 'customer_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('customer_documents'); }
};
