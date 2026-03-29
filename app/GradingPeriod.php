<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GradingPeriod extends Model
{
    protected $fillable = [
        'school_year',
        'semester',
        'section_subject_faculty',
        'period',
        'description',
        'percentage',
        'start_date',
        'end_date',
        'start_time',
        'grading_computation',
        'use_grades_library',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'use_grades_library' => 'boolean',
    ];
}
