<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_submissions', function (Blueprint $table) {
            // 1. Buang Foreign Key Constraints dahulu
            // Format konvensyen Laravel: namaJadual_namaKolum_foreign
            $table->dropForeign(['user_id']);
            $table->dropForeign(['program_id']);

            // 2. Selepas FK dibuang, barulah padam kolum tersebut
            $table->dropColumn(['user_id', 'program_id']);
        });
    }

    public function down(): void
    {
        Schema::table('quiz_submissions', function (Blueprint $table) {
            // Revert balik kolum dan Foreign Key jika berlaku rollback
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('program_id')->nullable()->constrained()->cascadeOnDelete();
        });
    }
};
