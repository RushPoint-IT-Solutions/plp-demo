<?php

namespace App\Http\Requests;

use App\Course;
use Illuminate\Foundation\Http\FormRequest;

class SubmitApplicantApplicationRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $currentYear = (int) now()->year;

        $this->merge([
            'school_year' => $currentYear . '-' . ($currentYear + 1),
            'application_date' => now()->toDateString(),
        ]);
    }

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
            'last_name' => 'required|string|max:120',
            'first_name' => 'required|string|max:120',
            'middle_name' => 'nullable|string|max:120',
            'suffix' => 'nullable|string|max:40',
            'nickname' => 'nullable|string|max:120',
            'gender' => 'required|in:Male,Female',
            'nationality' => 'nullable|string|max:120',
            'religion' => 'nullable|string|max:120',
            'date_of_birth' => 'required|date',
            'place_of_birth' => 'nullable|string|max:190',
            'age' => 'nullable|integer|min:0|max:120',
            'civil_status' => 'nullable|string|max:60',
            'mobile_number' => 'required|digits:11',
            'email_address' => 'required|email|max:190',
            'present_street' => 'required|string|max:190',
            'present_barangay' => 'required|string|max:190',
            'present_zipcode' => 'required|string|max:20',
            'present_municipality' => 'required|string|max:190',
            'present_province' => 'required|string|max:190',
            'present_region' => 'required|string|max:190',
            'same_as_present' => 'nullable|boolean',
            'permanent_street' => 'required_unless:same_as_present,1|nullable|string|max:190',
            'permanent_barangay' => 'required_unless:same_as_present,1|nullable|string|max:190',
            'permanent_zipcode' => 'required_unless:same_as_present,1|nullable|string|max:20',
            'permanent_municipality' => 'required_unless:same_as_present,1|nullable|string|max:190',
            'permanent_province' => 'required_unless:same_as_present,1|nullable|string|max:190',
            'permanent_region' => 'required_unless:same_as_present,1|nullable|string|max:190',
            'photo' => 'nullable|image|max:3072',

            'junior_school' => 'required|string|max:190',
            'senior_school' => 'required_unless:no_k12,1|nullable|string|max:190',
            'shs_track_strand' => 'required|string|max:120',
            'no_k12' => 'nullable|boolean',
            'learner_reference_number' => 'required|digits:12',

            'mother_last_name' => 'required|string|max:120',
            'mother_first_name' => 'required|string|max:120',
            'mother_middle_name' => 'required|string|max:120',
            'mother_nationality' => 'required|string|max:120',
            'mother_religion' => 'required|string|max:120',
            'mother_date_of_birth' => 'required|date',
            'mother_mobile_number' => 'required|digits:11',
            'mother_occupation' => 'required|string|max:120',
            'mother_company_address' => 'required|string|max:190',
            'mother_estimated_monthly_income' => 'required|string|max:120',
            'mother_residence_address' => 'required|string|max:190',
            'mother_email_address' => 'nullable|email|max:190',
            'father_last_name' => 'required|string|max:120',
            'father_first_name' => 'required|string|max:120',
            'father_middle_name' => 'required|string|max:120',
            'father_nationality' => 'required|string|max:120',
            'father_religion' => 'required|string|max:120',
            'father_date_of_birth' => 'required|date',
            'father_mobile_number' => 'required|digits:11',
            'father_occupation' => 'required|string|max:120',
            'father_company_address' => 'required|string|max:190',
            'father_estimated_monthly_income' => 'required|string|max:120',
            'father_residence_address' => 'required|string|max:190',
            'father_email_address' => 'nullable|email|max:190',

            'apply_program' => 'required|in:college',
            'apply_strand' => 'nullable|string|max:120',
            'apply_course_id' => 'nullable|exists:courses,id',
            'entry_classification' => 'required|string|max:120',
            'year_level' => 'required|in:1st Year,2nd Year,3rd Year,4th Year,Grade 11,Grade 12',
            'semester' => 'required|string|max:60',
            'school_year' => 'required|string|max:20',
            'application_date' => 'required|date',
            'campus' => 'required|string|max:120',
        ];
    }

    public function messages()
    {
        return [
            'mobile_number.digits' => 'Mobile number must be exactly 11 digits.',
            'learner_reference_number.digits' => 'Learner reference number (LRN) must be exactly 12 digits.',
            'mother_mobile_number.digits' => 'Mother/Guardian mobile number must be exactly 11 digits.',
            'father_mobile_number.digits' => 'Father/Guardian mobile number must be exactly 11 digits.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->input('apply_program') !== 'college') {
                return;
            }

            if (!Course::query()->exists()) {
                return;
            }

            if (empty($this->input('apply_course_id'))) {
                $validator->errors()->add('apply_course_id', 'Course is required when applying for college.');
            }
        });
    }
}
