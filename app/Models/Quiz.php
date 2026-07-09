<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quiz extends Model
{
   protected $fillable = [
        'question',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct_answer',
        'extras',
        'created_at',
        'updated_at',
        'quiz_type',
        'program_id'
    ];


    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }
}
