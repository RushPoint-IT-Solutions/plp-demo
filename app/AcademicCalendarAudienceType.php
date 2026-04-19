<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AcademicCalendarAudienceType extends Model
{
    protected $fillable = [
        'code',
        'label',
    ];

    public function events()
    {
        return $this->belongsToMany(
            AcademicCalendarEvent::class,
            'academic_calendar_event_audiences',
            'audience_type_id',
            'academic_calendar_event_id'
        )->withTimestamps();
    }
}
