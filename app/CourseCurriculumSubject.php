<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CourseCurriculumSubject extends Model
{
    protected $fillable = [
        'course_curriculum_id',
        'subject_id',
        'year_block_id',
        'semester_id',
        'credited_units',
        'display_order',
    ];

    public function curriculum()
    {
        return $this->belongsTo(CourseCurriculum::class, 'course_curriculum_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function yearBlock()
    {
        return $this->belongsTo(YearBlock::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function requisites()
    {
        return $this->hasMany(CurriculumSubjectRequisite::class, 'course_curriculum_subject_id');
    }
}
