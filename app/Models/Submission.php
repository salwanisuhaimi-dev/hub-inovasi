<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Submission extends Model
{
    use HasFactory;

    /**
     *
     */
    protected $fillable = [
        'program_id',
        'user_id',
    ];

    /**
     * Relationship
     * $submission->program->title
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**D
     * Relationship
     * $submission->user->name
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
