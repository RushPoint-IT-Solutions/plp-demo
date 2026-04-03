<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveApplicantStep3Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'mother_last_name' => 'nullable|string|max:120',
            'mother_first_name' => 'nullable|string|max:120',
            'mother_middle_name' => 'nullable|string|max:120',
            'mother_nationality' => 'nullable|string|max:120',
            'mother_religion' => 'nullable|string|max:120',
            'mother_date_of_birth' => 'nullable|date',
            'mother_mobile_number' => 'nullable|digits:11',
            'mother_occupation' => 'nullable|string|max:120',
            'mother_company_address' => 'nullable|string|max:190',
            'mother_estimated_monthly_income' => 'nullable|string|max:120',
            'mother_residence_address' => 'nullable|string|max:190',
            'mother_email_address' => 'nullable|email|max:190',
            'father_last_name' => 'nullable|string|max:120',
            'father_first_name' => 'nullable|string|max:120',
            'father_middle_name' => 'nullable|string|max:120',
            'father_nationality' => 'nullable|string|max:120',
            'father_religion' => 'nullable|string|max:120',
            'father_date_of_birth' => 'nullable|date',
            'father_mobile_number' => 'nullable|digits:11',
            'father_occupation' => 'nullable|string|max:120',
            'father_company_address' => 'nullable|string|max:190',
            'father_estimated_monthly_income' => 'nullable|string|max:120',
            'father_residence_address' => 'nullable|string|max:190',
            'father_email_address' => 'nullable|email|max:190',
        ];
    }

    public function messages()
    {
        return [
            'mother_mobile_number.digits' => 'Mother/Guardian mobile number must be exactly 11 digits.',
            'father_mobile_number.digits' => 'Father/Guardian mobile number must be exactly 11 digits.',
        ];
    }
}
