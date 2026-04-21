<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApplicantRequirementSubmissionFile extends Model
{
    protected $fillable = [
        'applicant_requirement_submission_id',
        'uploaded_by_user_id',
        'original_filename',
        'storage_disk',
        'storage_path',
        'mime_type',
        'size_bytes',
    ];

    protected $casts = [
        'applicant_requirement_submission_id' => 'integer',
        'uploaded_by_user_id' => 'integer',
        'size_bytes' => 'integer',
    ];

    public function submission()
    {
        return $this->belongsTo(ApplicantRequirementSubmission::class, 'applicant_requirement_submission_id');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
