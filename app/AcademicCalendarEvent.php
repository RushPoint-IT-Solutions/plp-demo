<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AcademicCalendarEvent extends Model
{
    protected $fillable = [
        'event_date',
        'time_from',
        'time_to',
        'title',
        'venue',
        'in_charge',
        'post_until',
        'event_type',
        'is_active',
    ];

    protected $casts = [
        'event_date' => 'date',
        'post_until' => 'date',
        'is_active' => 'boolean',
    ];
}
