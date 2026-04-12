<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StudentGradeRecord extends Model
{
    protected $table = 'student_grade_records';

    protected $fillable = [
        'student_id',
        'student_no',
        'school_year',
        'term',
        'subject_code',
        'equiv_subject_code',
        'professor',
        'description',
        'units',
        'status',
        'section_code',
        'final_grade',
        'inc',
        'grade_status',
        'remarks',
    ];

    protected $casts = [
        'units' => 'float',
        'final_grade' => 'float',
        'inc' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
