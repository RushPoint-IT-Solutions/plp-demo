<?php

namespace App;

use App\Concerns\ResolvesLookupCodeFields;
use Illuminate\Database\Eloquent\Model;

class AcademicCalendarEvent extends Model
{
    use ResolvesLookupCodeFields;

    protected $fillable = [
        'event_date',
        'time_from',
        'time_to',
        'title',
        'venue',
        'in_charge',
        'post_until',
        'event_type',
        'event_type_id',
        'is_active',
    ];

    protected $casts = [
        'event_date' => 'date',
        'post_until' => 'date',
        'is_active' => 'boolean',
    ];

    public function eventTypeLookup()
    {
        return $this->belongsTo(AcademicEventType::class, 'event_type_id');
    }

    public function getEventTypeAttribute($value)
    {
        return $this->getLookupCodeAttributeValue('event_type', 'eventTypeLookup', $value);
    }

    public function setEventTypeAttribute($value)
    {
        $this->setLookupCodeAttributeValue('event_type', 'event_type_id', AcademicEventType::class, $value);
    }
}
