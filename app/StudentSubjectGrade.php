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
        'remarks',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
