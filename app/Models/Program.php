<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Program extends Model
{
    use HasFactory;

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    protected $fillable = [
        'title',
        'category_id',
        'description',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'location',
        'deadline',
        'status',
        'image_path',
        'publication_id',
        'form_publication_id',
        'time_limit'
    ];

    public function category()
    {
        return $this->belongsTo(ProgramType::class, 'category_id');
    }

    public function publication()
    {
        return $this->belongsTo(Publication::class);
    }

    public function formPublication()
    {
        return $this->belongsTo(Publication::class, 'form_publication_id');
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }
}
