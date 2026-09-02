<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('full_name', 100);
            $table->string('phone', 20);
            $table->string('email', 150)->nullable();
            $table->enum('source', ['website','facebook','instagram','whatsapp','olx','pakwheels','referral','walk_in','other'])->default('walk_in');
            $table->string('vehicle_interest', 200)->nullable();
            $table->decimal('budget', 12, 2)->nullable();
            $table->enum('status', ['new','contacted','qualified','negotiation','won','lost'])->default('new');
            $table->string('lost_reason', 255)->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->timestamp('next_follow_up')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users');
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'assigned_to']);
            $table->index(['tenant_id', 'next_follow_up']);
        });
    }
    public function down(): void { Schema::dropIfExists('leads'); }
};
