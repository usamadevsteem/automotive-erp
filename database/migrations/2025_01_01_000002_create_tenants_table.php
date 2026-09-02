<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('subdomain', 50)->unique();
            $table->string('company_name', 150);
            $table->string('owner_name', 100);
            $table->string('email', 150)->unique();
            $table->string('phone', 20);
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('logo_path', 255)->nullable();
            $table->foreignId('plan_id')->constrained('plans');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('plan_expires_at')->nullable();
            $table->enum('status', ['trial','active','suspended','cancelled'])->default('trial');
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->index('subdomain');
            $table->index('status');
        });
    }
    public function down(): void { Schema::dropIfExists('tenants'); }
};
