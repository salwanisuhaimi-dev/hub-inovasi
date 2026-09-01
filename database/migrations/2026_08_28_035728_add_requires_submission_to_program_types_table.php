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
         Schema::table('program_types', function (Blueprint $table) {
             $table->boolean('requires_submission')->default(true)->after('submission_slug');
         });
     }

     public function down(): void
     {
         Schema::table('program_types', function (Blueprint $table) {
             $table->dropColumn('requires_submission');
         });
     }
};
