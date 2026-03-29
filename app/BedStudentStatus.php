<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BedStudentStatus extends Model
{
    protected $fillable = [
        'student_no',
        'student_name',
        'course',
        'year_level',
        'section',
        'school_year',
        'term',
        'no_payment',
        'no_section',
    ];

    protected $casts = [
        'no_payment' => 'boolean',
        'no_section' => 'boolean',
    ];
}
