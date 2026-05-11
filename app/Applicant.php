<?php

namespace App;

use App\Concerns\ResolvesLookupCodeFields;
use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    use ResolvesLookupCodeFields;

    protected $fillable = [
        'applicant_id',
        'lrn', 'last_name', 'first_name', 'middle_name', 'suffix', 'nickname',
        'gender', 'nationality', 'religion', 'date_of_birth', 'place_of_birth',
        'age', 'civil_status', 'mobile_number', 'email_address', 'photo',
        'present_street', 'present_barangay', 'present_zipcode',
        'present_municipality', 'present_province', 'present_region',
        'present_location_address_id',
        'same_as_present',
        'permanent_street', 'permanent_barangay', 'permanent_zipcode',
        'permanent_municipality', 'permanent_province', 'permanent_region',
        'permanent_location_address_id',
        'exam_date', 'exam_room', 'exam_result_status', 'exam_score',
        'interview_date', 'interview_room', 'interview_status', 'medical_clearance_status',
        'exam_result_status_id',
        'application_status', 'application_draft_step', 'application_submitted_at', 'application_portal_stage',
        'application_status_id',
        'college_id',
    ];

    protected $casts = [
        'date_of_birth'   => 'date',
        'exam_date'       => 'datetime',
        'interview_date'  => 'datetime',
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

    public function onboardingAcknowledgement()
    {
        return $this->hasOne(ApplicantOnboardingAcknowledgement::class);
    }

    public function photoUpload()
    {
        return $this->hasOne(ApplicantPhotoUpload::class);
    }

    public function requirementSubmissions()
    {
        return $this->hasMany(ApplicantRequirementSubmission::class);
    }

    public function applicationStatusLookup()
    {
        return $this->belongsTo(ApplicantApplicationStatus::class, 'application_status_id');
    }

    public function examResultStatusLookup()
    {
        return $this->belongsTo(ApplicantExamResultStatus::class, 'exam_result_status_id');
    }

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function getApplicationStatusAttribute($value)
    {
        return $this->getLookupCodeAttributeValue('application_status', 'applicationStatusLookup', $value);
    }

    public function setApplicationStatusAttribute($value)
    {
        $this->setLookupCodeAttributeValue(
            'application_status',
            'application_status_id',
            ApplicantApplicationStatus::class,
            $value
        );
    }

    public function getExamResultStatusAttribute($value)
    {
        return $this->getLookupCodeAttributeValue('exam_result_status', 'examResultStatusLookup', $value);
    }

    public function setExamResultStatusAttribute($value)
    {
        $this->setLookupCodeAttributeValue(
            'exam_result_status',
            'exam_result_status_id',
            ApplicantExamResultStatus::class,
            $value
        );
    }
}
