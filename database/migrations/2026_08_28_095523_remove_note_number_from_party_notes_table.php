<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('party_notes', function (Blueprint $table) {
            // Remove the unique constraint first
            $table->dropUnique(['tenant_id', 'note_number']);

            // Remove note number column
            $table->dropColumn('note_number');
        });
    }

    public function down(): void
    {
        Schema::table('party_notes', function (Blueprint $table) {
            // Restore note number column
            $table->string('note_number')->nullable();

            // Restore unique constraint
            $table->unique(['tenant_id', 'note_number']);
        });
    }
};