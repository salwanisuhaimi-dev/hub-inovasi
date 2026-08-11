<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendance_submissions', function (Blueprint $table) {
            $table->foreignId('target_submission_id')
                ->nullable()
                ->after('submission_id')
                ->constrained('submissions')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_submissions', function (Blueprint $table) {
            $table->dropForeign(['target_submission_id']);
            $table->dropColumn('target_submission_id');
        });
    }
};
