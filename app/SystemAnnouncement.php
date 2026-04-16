<?php

namespace App;

use App\Concerns\ResolvesLookupCodeFields;
use App\Concerns\ResolvesProgramYearDimensions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class SystemAnnouncement extends Model
{
    use ResolvesLookupCodeFields, ResolvesProgramYearDimensions;

    const AUDIENCE_EVERYONE = 'everyone';
    const AUDIENCE_STUDENTS = 'students';
    const AUDIENCE_FACULTY = 'faculty';
    const AUDIENCE_STAFF = 'staff';
    const AUDIENCE_APPLICANT = 'applicant';

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
            self::normalizeAudienceCode($value)
        );
    }

    public function scopeActiveOn(Builder $query, $date = null)
    {
        $targetDate = now()->toDateString();
        if ($date !== null && $date !== '') {
            $timestamp = strtotime((string) $date);
            if ($timestamp !== false) {
                $targetDate = date('Y-m-d', $timestamp);
            }
        }

        return $query
            ->where(function (Builder $dateQuery) use ($targetDate) {
                $dateQuery->whereNull('date_from')
                    ->orWhereDate('date_from', '<=', $targetDate);
            })
            ->where(function (Builder $dateQuery) use ($targetDate) {
                $dateQuery->whereNull('date_to')
                    ->orWhereDate('date_to', '>=', $targetDate);
            });
    }

    public function scopeVisibleToAudience(Builder $query, $audienceCode)
    {
        if (!Schema::hasTable('announcement_types')
            || !Schema::hasColumn($this->getTable(), 'announcement_type_id')) {
            return $query;
        }

        $normalizedAudienceCode = self::normalizeAudienceCode($audienceCode);
        $allowedCodes = array_values(array_unique(array_filter([
            self::AUDIENCE_EVERYONE,
            $normalizedAudienceCode,
        ])));

        $typeIds = AnnouncementType::query()
            ->where(function (Builder $lookupQuery) use ($allowedCodes) {
                foreach ($allowedCodes as $code) {
                    $lookupQuery->orWhereRaw('LOWER(TRIM(code)) = ?', [$code]);
                }
            })
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter()
            ->values()
            ->all();

        return $query->where(function (Builder $visibilityQuery) use ($typeIds) {
            $visibilityQuery->whereNull('announcement_type_id');

            if (!empty($typeIds)) {
                $visibilityQuery->orWhereIn('announcement_type_id', $typeIds);
            }
        });
    }

    public static function allowedAudienceCodes(): array
    {
        return [
            self::AUDIENCE_EVERYONE,
            self::AUDIENCE_STUDENTS,
            self::AUDIENCE_FACULTY,
            self::AUDIENCE_STAFF,
            self::AUDIENCE_APPLICANT,
        ];
    }

    public static function normalizeAudienceCode($value): string
    {
        $normalized = strtolower(trim((string) $value));

        $aliases = [
            'everyone' => self::AUDIENCE_EVERYONE,
            'all' => self::AUDIENCE_EVERYONE,
            'all users' => self::AUDIENCE_EVERYONE,
            'all user' => self::AUDIENCE_EVERYONE,
            'students' => self::AUDIENCE_STUDENTS,
            'student' => self::AUDIENCE_STUDENTS,
            'faculty' => self::AUDIENCE_FACULTY,
            'teacher' => self::AUDIENCE_FACULTY,
            'teachers' => self::AUDIENCE_FACULTY,
            'staff' => self::AUDIENCE_STAFF,
            'registrar' => self::AUDIENCE_STAFF,
            'admin' => self::AUDIENCE_STAFF,
            'administrator' => self::AUDIENCE_STAFF,
            'applicant' => self::AUDIENCE_APPLICANT,
            'applicants' => self::AUDIENCE_APPLICANT,
        ];

        if (isset($aliases[$normalized])) {
            return $aliases[$normalized];
        }

        return self::AUDIENCE_EVERYONE;
    }
}
