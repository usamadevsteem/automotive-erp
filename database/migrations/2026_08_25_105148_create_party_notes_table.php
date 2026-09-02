<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('party_notes', function (Blueprint $table) {
            $table->id();

            // Tenant / branch
            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('branch_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Party this note belongs to
            $table->foreignId('party_id')
                ->constrained()
                ->cascadeOnDelete();

            // Debit / credit
            $table->string('type');

            // Financial amount
            $table->decimal('amount', 15, 2);

            // Note date
            $table->date('note_date');

            // Reason / details
            $table->text('description')->nullable();

            // Optional link to another ERP record
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();

            // User who created the note
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['party_id', 'note_date']);
            $table->index(['tenant_id', 'type']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('party_notes');
    }
};