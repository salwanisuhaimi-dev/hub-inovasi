<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'submission_id',
        'total_questions',
        'correct_answers',
        'score',
        'time_taken',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }


}
