<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApplicantRequirementSubmission extends Model
{
    protected $fillable = [
        'applicant_id',
        'registrar_requirement_policy_id',
        'is_submitted',
        'remarks',
        'date_submitted',
        'verified_by_user_id',
    ];

    protected $casts = [
        'applicant_id' => 'integer',
        'registrar_requirement_policy_id' => 'integer',
        'is_submitted' => 'boolean',
        'date_submitted' => 'date',
        'verified_by_user_id' => 'integer',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }

    public function policy()
    {
        return $this->belongsTo(RegistrarRequirementPolicy::class, 'registrar_requirement_policy_id');
    }

    public function file()
    {
        return $this->hasOne(ApplicantRequirementSubmissionFile::class, 'applicant_requirement_submission_id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }
}
