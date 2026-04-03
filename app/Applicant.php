<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    protected $fillable = [
        'applicant_id',
        'lrn', 'last_name', 'first_name', 'middle_name', 'suffix', 'nickname',
        'gender', 'nationality', 'religion', 'date_of_birth', 'place_of_birth',
        'age', 'civil_status', 'mobile_number', 'email_address', 'photo',
        'present_street', 'present_barangay', 'present_zipcode',
        'present_municipality', 'present_province', 'present_region',
        'same_as_present',
        'permanent_street', 'permanent_barangay', 'permanent_zipcode',
        'permanent_municipality', 'permanent_province', 'permanent_region',
        'exam_date', 'exam_room', 'exam_result_status', 'exam_score',
        'application_status', 'application_draft_step', 'application_submitted_at', 'application_portal_stage',
    ];

    protected $casts = [
        'date_of_birth'   => 'date',
        'exam_date'       => 'datetime',
        'same_as_present' => 'boolean',
        'application_submitted_at' => 'datetime',
        'application_portal_stage' => 'integer',
    ];

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }

    public function user()
    {
        return $this->hasOne(User::class, 'applicant_id');
    }

    public function educationalBackground()
    {
        return $this->hasOne(ApplicantEducationalBackground::class);
    }

    public function familyBackground()
    {
        return $this->hasOne(ApplicantFamilyBackground::class);
    }

    public function applicationPreference()
    {
        return $this->hasOne(ApplicantApplicationPreference::class);
    }
}
