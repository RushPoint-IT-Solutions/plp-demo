<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveApplicantStep1Request extends FormRequest
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
        ];
    }

    public function messages()
    {
        return [
            'last_name.required' => 'Lastname is required.',
            'first_name.required' => 'First name is required.',
            'gender.required' => 'Gender is required.',
            'date_of_birth.required' => 'Date of birth is required.',
            'mobile_number.required' => 'Mobile number is required.',
            'mobile_number.digits' => 'Mobile number must be exactly 11 digits.',
            'email_address.required' => 'Email address is required.',
            'present_street.required' => 'Present street is required.',
            'present_barangay.required' => 'Present barangay is required.',
            'present_zipcode.required' => 'Present zipcode is required.',
            'present_municipality.required' => 'Present municipality/city is required.',
            'present_province.required' => 'Present province is required.',
            'present_region.required' => 'Present region is required.',
        ];
    }
}
