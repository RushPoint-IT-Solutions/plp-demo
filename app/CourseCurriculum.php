<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CourseCurriculum extends Model
{
    protected $table = 'course_curricula';

    protected $fillable = [
        'course_id',
        'curriculum_year_id',
        'curriculum_year_code',
        'date_from',
        'date_to',
        'title',
        'approval_status',
        'department_head_approved_at',
        'dean_approved_at',
        'registrar_approved_at',
        'academic_council_approved_at',
        'is_published',
        'published_at',
        'is_active',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'department_head_approved_at' => 'datetime',
        'dean_approved_at' => 'datetime',
        'registrar_approved_at' => 'datetime',
        'academic_council_approved_at' => 'datetime',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function curriculumYear()
    {
        return $this->belongsTo(CurriculumYear::class);
    }

    public function curriculumSubjects()
    {
        return $this->hasMany(CourseCurriculumSubject::class);
    }
}
