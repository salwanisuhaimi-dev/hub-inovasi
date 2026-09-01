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
            $table->string('other_submission_format')->default('notes')->after('category_id');

            $table->string('submission_external_link')->nullable()->after('other_submission_format');

            $table->string('submission_pdf_form')->nullable()->after('submission_external_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
              $table->dropColumn([
                          'other_submission_format',
                          'submission_external_link',
                          'submission_pdf_form',
              ]);
        });
    }
};
