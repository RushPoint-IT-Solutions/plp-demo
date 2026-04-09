<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CourseCurriculum extends Model
{
    protected $table = 'course_curricula';

    protected $fillable = [
        'course_id',
        'curriculum_year_code',
        'title',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function curriculumSubjects()
    {
        return $this->hasMany(CourseCurriculumSubject::class);
    }
}
