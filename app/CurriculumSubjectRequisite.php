<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CurriculumSubjectRequisite extends Model
{
    protected $fillable = [
        'course_curriculum_subject_id',
        'requisite_subject_id',
        'curriculum_requisite_type_id',
        'sort_order',
    ];

    public function curriculumSubject()
    {
        return $this->belongsTo(CourseCurriculumSubject::class, 'course_curriculum_subject_id');
    }

    public function requisiteSubject()
    {
        return $this->belongsTo(Subject::class, 'requisite_subject_id');
    }

    public function requisiteType()
    {
        return $this->belongsTo(CurriculumRequisiteType::class, 'curriculum_requisite_type_id');
    }
}
