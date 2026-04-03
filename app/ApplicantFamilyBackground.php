<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApplicantFamilyBackground extends Model
{
    protected $fillable = [
        'applicant_id',
        'mother_last_name',
        'mother_first_name',
        'mother_middle_name',
        'mother_nationality',
        'mother_religion',
        'mother_date_of_birth',
        'mother_mobile_number',
        'mother_occupation',
        'mother_company_address',
        'mother_estimated_monthly_income',
        'mother_residence_address',
        'mother_email_address',
        'father_last_name',
        'father_first_name',
        'father_middle_name',
        'father_nationality',
        'father_religion',
        'father_date_of_birth',
        'father_mobile_number',
        'father_occupation',
        'father_company_address',
        'father_estimated_monthly_income',
        'father_residence_address',
        'father_email_address',
    ];

    protected $casts = [
        'mother_date_of_birth' => 'date',
        'father_date_of_birth' => 'date',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
}
