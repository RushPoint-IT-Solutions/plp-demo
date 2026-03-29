<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StudentUpdateRun extends Model
{
    protected $fillable = [
        'action_name',
        'run_mode',
        'school_year',
        'term',
        'period',
        'operator',
        'course',
        'year_level',
        'section',
        'student_no',
        'include_unpaid_only',
        'active_only',
        'affected_count',
    ];

    protected $casts = [
        'include_unpaid_only' => 'boolean',
        'active_only' => 'boolean',
    ];
}
