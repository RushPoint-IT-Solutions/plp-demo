<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApplicantEducationalBackground extends Model
{
    protected $fillable = [
        'applicant_id',
        'junior_school',
        'senior_school',
        'shs_track_strand',
        'no_k12',
        'learner_reference_number',
    ];

    protected $casts = [
        'no_k12' => 'boolean',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
}
