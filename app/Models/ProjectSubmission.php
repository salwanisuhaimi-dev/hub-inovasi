<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectSubmission extends Model
{
    use HasFactory;

    /**
     *
     */
    protected $fillable = [
        'submission_id',
        'department_id',
        'project_title',
        'project_description',
        'group_name',
        'total_members',
        'file_path',
        'status',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

}
