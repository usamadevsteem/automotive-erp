<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('whatsapp_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('phone_number', 20);
            $table->string('display_name', 100)->nullable();
            $table->enum('provider', ['meta_cloud_api','twilio','waapi'])->default('meta_cloud_api');
            $table->text('api_key')->nullable();
            $table->string('webhook_token', 100)->nullable();
            $table->enum('status', ['active','inactive','banned'])->default('active');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index('tenant_id');
        });

        Schema::create('whatsapp_conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('wa_account_id');
            $table->string('customer_phone', 20);
            $table->string('customer_name', 100)->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('lead_id')->nullable();
            $table->enum('status', ['open','assigned','resolved','spam'])->default('open');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->smallInteger('unread_count')->default(0);
            $table->timestamp('last_message_at')->nullable();
            $table->string('last_message_preview', 200)->nullable();
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('wa_account_id')->references('id')->on('whatsapp_accounts')->cascadeOnDelete();
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
            $table->foreign('lead_id')->references('id')->on('leads')->nullOnDelete();
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
            $table->unique(['tenant_id','wa_account_id','customer_phone']);
            $table->index(['tenant_id','status']);
            $table->index(['tenant_id','assigned_to']);
        });

        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->foreignId('conversation_id')->constrained('whatsapp_conversations')->cascadeOnDelete();
            $table->string('wa_message_id', 100)->nullable();
            $table->enum('direction', ['inbound','outbound'])->default('inbound');
            $table->enum('message_type', ['text','image','document','audio','video','template'])->default('text');
            $table->text('content')->nullable();
            $table->string('media_url', 500)->nullable();
            $table->string('media_path', 500)->nullable();
            $table->string('template_name', 100)->nullable();
            $table->enum('status', ['sent','delivered','read','failed'])->default('sent');
            $table->unsignedBigInteger('sent_by')->nullable();
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('sent_by')->references('id')->on('users')->nullOnDelete();
            $table->unique('wa_message_id');
            $table->index('conversation_id');
            $table->index(['tenant_id','sent_at']);
        });

        Schema::create('whatsapp_quick_replies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('title', 100);
            $table->text('body');
            $table->string('category', 50)->nullable();
            $table->integer('usage_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users');
            $table->index('tenant_id');
        });
    }

    public function down(): void {
        Schema::dropIfExists('whatsapp_quick_replies');
        Schema::dropIfExists('whatsapp_messages');
        Schema::dropIfExists('whatsapp_conversations');
        Schema::dropIfExists('whatsapp_accounts');
    }
};
