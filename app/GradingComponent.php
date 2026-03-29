<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GradingComponent extends Model
{
    protected $fillable = [
        'school_year',
        'period',
        'semester',
        'section',
        'course_code',
        'title',
        'sequence_no',
        'percentage',
        'lab_mode',
        'cap',
        'updated_by',
        'effective_date',
    ];

    protected $casts = [
        'effective_date' => 'date',
    ];
}
