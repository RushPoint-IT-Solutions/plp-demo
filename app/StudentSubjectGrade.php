<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StudentSubjectGrade extends Model
{
    protected $fillable = [
        'subject_id',
        'student_id',
        'prelim',
        'midterm',
        'final',
        'final_average',
        'status',
        'grade_rule_id',
        'remarks',
        'draft_saved_at',
        'midterm_posted_at',
        'final_posted_at',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function gradeRule()
    {
        return $this->belongsTo(GradeRule::class);
    }
}
