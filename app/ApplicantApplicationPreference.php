<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApplicantApplicationPreference extends Model
{
    protected $fillable = [
        'applicant_id',
        'apply_program',
        'apply_strand',
        'apply_course_id',
        'entry_classification',
        'year_level',
        'semester',
        'school_year',
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
}
