<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StudentDisciplineStudentType extends Model
{
    protected $fillable = [
        'code',
        'label',
    ];

    public function disciplineStudents()
    {
        return $this->hasMany(StudentDisciplineStudent::class, 'student_type_id');
    }
}
