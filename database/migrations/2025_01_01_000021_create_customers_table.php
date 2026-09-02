<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('full_name', 100);
            $table->string('father_husband_name', 100)->nullable();
            $table->string('cnic', 20)->nullable();
            $table->string('mobile', 20);
            $table->string('mobile_alt', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('occupation', 100)->nullable();
            $table->enum('customer_type', ['buyer','seller','both'])->default('buyer');
            $table->enum('source', ['walk_in','referral','website','facebook','instagram','whatsapp','olx','pakwheels','other'])->default('walk_in');
            $table->enum('tax_status', ['filer','non_filer','unknown'])->default('unknown');
            $table->string('ntn_number', 20)->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users');
            $table->index(['tenant_id', 'mobile']);
            $table->index(['tenant_id', 'cnic']);
            $table->index(['tenant_id', 'assigned_to']);
        });
    }
    public function down(): void { Schema::dropIfExists('customers'); }
};
