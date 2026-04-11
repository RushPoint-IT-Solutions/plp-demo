<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StudentDisciplineStudent extends Model
{
    protected $fillable = [
        'student_id',
        'course_id',
        'student_type_id',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function studentType()
    {
        return $this->belongsTo(StudentDisciplineStudentType::class, 'student_type_id');
    }

    public function records()
    {
        return $this->hasMany(StudentDisciplineRecord::class, 'discipline_student_id');
    }
}
