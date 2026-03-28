<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterStudentGradeFile extends Model
{
    protected $table = 'master_student_grade_files';

    protected $fillable = [
        'student_no',
        'student_name',
        'course',
        'year_level',
    ];
}
