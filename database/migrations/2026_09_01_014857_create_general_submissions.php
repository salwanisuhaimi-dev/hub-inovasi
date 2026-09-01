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
        Schema::create('general_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->unique()->constrained('submissions')->cascadeOnDelete();
            $table->string('submission_type')->default('file');
            $table->string('file_path')->nullable();
            $table->string('external_link')->nullable();
            $table->text('notes')->nullable();                  
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_submissions');
    }
};
