<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CurriculumYear extends Model
{
    protected $fillable = [
        'code',
        'label',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function courseCurricula()
    {
        return $this->hasMany(CourseCurriculum::class);
    }
}
