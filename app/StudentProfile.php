<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    protected $table = 'student_profiles';

    protected $fillable = [
        'student_no', 'first_name', 'last_name', 'middle_name', 'suffix', 'nickname',
        'gender', 'nationality', 'nationality_other', 'religion', 'religion_other',
        'date_of_birth', 'place_of_birth', 'civil_status', 'mobile_number',
        'student_email', 'profile_photo_path',

        'present_street', 'present_barangay', 'present_zipcode',
        'present_municipality', 'present_province', 'present_region',
        'permanent_street', 'permanent_barangay', 'permanent_zipcode',
        'permanent_municipality', 'permanent_province', 'permanent_region',
        'same_as_present', 'is_orphan', 'is_first_gen', 'is_4ps',
        'has_disability', 'is_foreign',

        'mother_firstname', 'mother_middlename', 'mother_lastname',
        'mother_contact', 'mother_occupation', 'mother_pensioner',
        'father_firstname', 'father_middlename', 'father_lastname',
        'father_contact', 'father_occupation', 'father_pensioner',
        'guardian_firstname', 'guardian_middlename', 'guardian_lastname',
        'guardian_contact', 'guardian_occupation', 'guardian_address',
        'parent_marital_status', 'monthly_family_income',
        'number_of_siblings', 'household_members', 'dependents',

        'junior_school', 'senior_school', 'shs_track_strand', 'no_k12', 'lrn',

        'family_income_source', 'family_income_source_other',
        'living_situation', 'living_situation_other',
        'working_student', 'has_scholarship', 'first_in_family_college',
        'internet_access', 'it_tools_access',
        'devices', 'devices_other',
        'lms_used', 'lms_used_other',
        'lms_preferred', 'lms_preferred_other',
        'lms_reasons', 'lms_reasons_other',
        'preferred_class_time', 'evening_classes',
        'profile_complete',
    ];

    protected $casts = [
        'date_of_birth'    => 'date',
        'same_as_present'  => 'boolean',
        'is_orphan'        => 'boolean',
        'is_first_gen'     => 'boolean',
        'is_4ps'           => 'boolean',
        'has_disability'   => 'boolean',
        'is_foreign'       => 'boolean',
        'mother_pensioner' => 'boolean',
        'father_pensioner' => 'boolean',
        'no_k12'           => 'boolean',
        'profile_complete' => 'boolean',
    ];

    public function profileImage()
    {
        return $this->hasOne(StudentProfileImage::class);
    }
}
