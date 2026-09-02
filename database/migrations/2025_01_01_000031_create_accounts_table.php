<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('account_code', 20);
            $table->string('account_name', 150);
            $table->enum('account_type', ['asset','liability','equity','revenue','expense']);
            $table->string('account_subtype', 50)->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('description', 255)->nullable();
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('parent_id')->references('id')->on('accounts')->nullOnDelete();
            $table->unique(['tenant_id', 'account_code']);
            $table->index(['tenant_id', 'account_type']);
        });
    }
    public function down(): void { Schema::dropIfExists('accounts'); }
};
