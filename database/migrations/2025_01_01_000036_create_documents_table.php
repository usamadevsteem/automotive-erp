<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('name', 150);
            $table->enum('document_type', [
                'sale_invoice','proforma_invoice','booking_receipt','payment_receipt',
                'delivery_order','affidavit','transfer_letter','open_transfer_letter',
                'sale_agreement','possession_letter','authority_letter','handover_certificate',
                'customer_declaration','installment_agreement','commission_voucher',
                'cash_receipt_voucher','cash_payment_voucher','journal_voucher'
            ]);
            $table->longText('html_body');
            $table->text('header_html')->nullable();
            $table->text('footer_html')->nullable();
            $table->enum('page_size', ['A4','A5','Letter'])->default('A4');
            $table->enum('orientation', ['portrait','landscape'])->default('portrait');
            $table->boolean('show_logo')->default(true);
            $table->boolean('show_qr')->default(false);
            $table->string('watermark_text', 50)->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users');
            $table->index(['tenant_id', 'document_type']);
        });

        Schema::create('generated_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('document_number', 30);
            $table->unsignedBigInteger('template_id');
            $table->string('document_type', 50);
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->json('resolved_data');
            $table->string('file_path', 500)->nullable();
            $table->string('verification_code', 30)->unique();
            $table->unsignedBigInteger('generated_by');
            $table->timestamp('generated_at');
            $table->timestamp('printed_at')->nullable();
            $table->timestamp('emailed_at')->nullable();
            $table->timestamp('whatsapp_sent_at')->nullable();
            $table->boolean('is_voided')->default(false);
            $table->unsignedBigInteger('voided_by')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->string('void_reason', 255)->nullable();
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->foreign('template_id')->references('id')->on('document_templates');
            $table->foreign('generated_by')->references('id')->on('users');
            $table->foreign('voided_by')->references('id')->on('users')->nullOnDelete();
            $table->unique(['tenant_id', 'document_number']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['tenant_id', 'document_type']);
        });

        Schema::create('deal_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('deal_number', 30);
            $table->foreignId('sale_invoice_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->enum('deal_type', ['cash','installment','trade_in'])->default('cash');
            $table->json('checklist')->nullable();
            $table->enum('status', ['in_progress','complete'])->default('in_progress');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unique(['tenant_id', 'deal_number']);
            $table->index(['tenant_id', 'customer_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('deal_files');
        Schema::dropIfExists('generated_documents');
        Schema::dropIfExists('document_templates');
    }
};
