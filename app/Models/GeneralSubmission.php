<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneralSubmission extends Model
{
    use HasFactory;

    /**
     *
     */
    protected $fillable = [
        'submission_id',
        'submission_type',
        'file_path',
        'external_link',
        'notes'
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

}
