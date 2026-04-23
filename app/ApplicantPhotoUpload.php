<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApplicantPhotoUpload extends Model
{
    protected $fillable = [
        'applicant_id',
        'uploaded_by_user_id',
        'original_filename',
        'storage_disk',
        'storage_path',
        'mime_type',
        'size_bytes',
    ];

    protected $casts = [
        'applicant_id' => 'integer',
        'uploaded_by_user_id' => 'integer',
        'size_bytes' => 'integer',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
