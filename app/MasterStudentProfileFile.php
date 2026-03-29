<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterStudentProfileFile extends Model
{
    protected $table = 'master_student_profiles';

    protected $fillable = [
        'student_no',
        'student_name',
        'course',
        'year_level',
    ];
}
