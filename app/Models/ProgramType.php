<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'category',
        'level',
        'description',
        'is_active',
        'submission_slug',
    ];

    public function programs()
    {
        return $this->hasMany(Program::class, 'category_id');
    }
}
