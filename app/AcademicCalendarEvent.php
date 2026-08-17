<?php

namespace App;

use App\Concerns\ResolvesLookupCodeFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class AcademicCalendarEvent extends Model
{
    use ResolvesLookupCodeFields;

    protected $fillable = [
        'event_date',
        'date_from',
        'date_to',
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
        'date_from' => 'date',
        'date_to' => 'date',
        'post_until' => 'date',
        'is_active' => 'boolean',
    ];

    public function eventTypeLookup()
    {
        return $this->belongsTo(AcademicEventType::class, 'event_type_id');
    }

    public function audienceTypes()
    {
        return $this->belongsToMany(
            AcademicCalendarAudienceType::class,
            'academic_calendar_event_audiences',
            'academic_calendar_event_id',
            'audience_type_id'
        )->withTimestamps();
    }

    public function scopeVisibleToAudience($query, $audienceCode)
    {
        $normalizedCode = strtolower(trim((string) $audienceCode));
        if ($normalizedCode === '') {
            return $query;
        }

        if (!Schema::hasTable('academic_calendar_event_audiences')
            || !Schema::hasTable('academic_calendar_audience_types')) {
            return $query;
        }

        return $query->where(function ($innerQuery) use ($normalizedCode) {
            $innerQuery->whereHas('audienceTypes', function ($audienceQuery) use ($normalizedCode) {
                $audienceQuery->whereRaw('LOWER(code) = ?', [$normalizedCode]);
            })->orWhereDoesntHave('audienceTypes');
        });
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
