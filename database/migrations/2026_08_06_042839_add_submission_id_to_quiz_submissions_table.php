<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_submissions', function (Blueprint $table) {
            $table->foreignId('submission_id')
                  ->nullable() // Guna nullable() jika jadual sudah ada data
                  ->after('id')
                  ->unique()
                  ->constrained('submissions')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('quiz_submissions', function (Blueprint $table) {
            $table->dropForeign(['submission_id']);
            $table->dropColumn('submission_id');
        });
    }
};
