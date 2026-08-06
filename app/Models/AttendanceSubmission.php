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
        'submission_id',
        'dept_id',
        'pdf_path',
    ];


    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

}
