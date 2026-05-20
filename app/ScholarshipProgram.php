<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScholarshipProgram extends Model
{
    protected $fillable = [
        'name',
        'category',
        'coverage_type',
        'coverage_value',
        'school_year',
        'semester',
        'applicable_course_ids',
        'year_level_eligibility',
        'available_slots',
        'maintaining_gwa',
        'status',
        'description',
    ];

    protected $casts = [
        'coverage_value' => 'decimal:2',
        'maintaining_gwa' => 'decimal:2',
        'available_slots' => 'integer',
    ];

    public function scholarTags()
    {
        return $this->hasMany(ScholarshipStudent::class);
    }

    public function getApplicableCourseIdsArrayAttribute(): array
    {
        $value = $this->applicable_course_ids;
        if (!$value) {
            return [];
        }

        $decoded = json_decode($value, true);
        return is_array($decoded) ? array_values(array_filter($decoded)) : [];
    }
}
