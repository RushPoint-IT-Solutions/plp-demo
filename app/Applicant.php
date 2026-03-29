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
    ];

    protected $casts = [
        'date_of_birth'   => 'date',
        'exam_date'       => 'datetime',
        'same_as_present' => 'boolean',
    ];

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }

    public function user()
    {
        return $this->hasOne(User::class, 'applicant_id');
    }
}
