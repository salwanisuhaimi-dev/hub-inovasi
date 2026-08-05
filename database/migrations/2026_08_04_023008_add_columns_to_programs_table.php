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
        Schema::table('programs', function (Blueprint $table) {
          // Visibility Mode: 'all', 'program_participants', 'specific_submissions'
                      $table->string('visibility_type')
                            ->default('all')
                            ->after('description');

                      // Array of target Program IDs (e.g. [1, 3] = visible to anyone who submitted to Program #1 or #3)
                      $table->json('target_program_ids')
                            ->nullable()
                            ->after('visibility_type');

                      // Array of specific Submission IDs (e.g. [10, 25, 42] = visible ONLY to users of these exact submission rows)
                      $table->json('target_submission_ids')
                            ->nullable()
                            ->after('target_program_ids');
      });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
              $table->dropColumn([
                  'visibility_type',
                  'target_program_ids',
                  'target_submission_ids',
              ]);
        });
    }
};
