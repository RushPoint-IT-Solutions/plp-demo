<?php

namespace App;

use App\Concerns\ResolvesAcademicTerm;
use App\Concerns\ResolvesLookupCodeFields;
use Illuminate\Database\Eloquent\Model;

class ApplicantApplicationPreference extends Model
{
    use ResolvesAcademicTerm, ResolvesLookupCodeFields;

    protected $fillable = [
        'applicant_id',
        'apply_program',
        'apply_strand',
        'apply_course_id',
        'entry_classification',
        'year_level',
        'year_level_id',
        'semester',
        'school_year',
        'academic_term_id',
        'application_date',
        'campus',
    ];

    protected $casts = [
        'application_date' => 'date',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'apply_course_id');
    }

    public function yearLevelLookup()
    {
        return $this->belongsTo(ApplicantYearLevel::class, 'year_level_id');
    }

    public function getYearLevelAttribute($value)
    {
        return $this->getLookupCodeAttributeValue('year_level', 'yearLevelLookup', $value);
    }

    public function setYearLevelAttribute($value)
    {
        $this->setLookupCodeAttributeValue('year_level', 'year_level_id', ApplicantYearLevel::class, $value);
    }
}
