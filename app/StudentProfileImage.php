<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StudentProfileImage extends Model
{
    protected $fillable = [
        'student_profile_id',
        'uploaded_by_user_id',
        'original_filename',
        'storage_disk',
        'storage_path',
        'mime_type',
        'size_bytes',
    ];

    protected $casts = [
        'student_profile_id' => 'integer',
        'uploaded_by_user_id' => 'integer',
        'size_bytes' => 'integer',
    ];

    public function studentProfile()
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
