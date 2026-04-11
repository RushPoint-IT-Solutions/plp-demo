<?php

namespace App;

use App\Concerns\ResolvesLookupCodeFields;
use App\Concerns\ResolvesProgramYearDimensions;
use Illuminate\Database\Eloquent\Model;

class SystemAnnouncement extends Model
{
    use ResolvesLookupCodeFields, ResolvesProgramYearDimensions;

    protected $fillable = [
        'date_from',
        'date_to',
        'title',
        'announcement_type',
        'announcement_type_id',
        'program',
        'course_id',
        'content',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
    ];

    public function announcementTypeLookup()
    {
        return $this->belongsTo(AnnouncementType::class, 'announcement_type_id');
    }

    public function canonicalCourse()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function getAnnouncementTypeAttribute($value)
    {
        return $this->getLookupCodeAttributeValue('announcement_type', 'announcementTypeLookup', $value);
    }

    public function setAnnouncementTypeAttribute($value)
    {
        $this->setLookupCodeAttributeValue(
            'announcement_type',
            'announcement_type_id',
            AnnouncementType::class,
            $value
        );
    }
}
