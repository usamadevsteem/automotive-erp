<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('slug', 50)->unique();
            $table->decimal('price_monthly', 10, 2);
            $table->decimal('price_annual', 10, 2);
            $table->smallInteger('max_vehicles')->unsigned();
            $table->tinyInteger('max_users')->unsigned();
            $table->tinyInteger('max_branches')->unsigned();
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('plans'); }
};
