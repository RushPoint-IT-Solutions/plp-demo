<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RegistrarEvaluationForm extends Model
{
    protected $fillable = [
        'name',
        'status',
        'responses',
        'academic_year',
        'program',
        'subject_code',
        'subject_name',
        'faculty_name',
        'period_from',
        'period_to',
        'target_respondents',
        'blocks',
        'published_at',
    ];

    protected $casts = [
        'responses' => 'integer',
        'blocks' => 'array',
        'period_from' => 'date',
        'period_to' => 'date',
        'published_at' => 'datetime',
    ];
}
