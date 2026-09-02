<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches');
            $table->string('name', 100);
            $table->string('email', 150);
            $table->string('phone', 20)->nullable();
            $table->string('password');
            $table->string('cnic', 20)->nullable();
            $table->string('designation', 100)->nullable();
            $table->string('profile_photo', 255)->nullable();
            $table->string('two_factor_secret', 100)->nullable();
            $table->boolean('two_factor_enabled')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['tenant_id', 'email']);
            $table->index('tenant_id');
            $table->index(['tenant_id', 'branch_id']);
            $table->index(['tenant_id', 'is_active']);
        });
    }
    public function down(): void { Schema::dropIfExists('users'); }
};
