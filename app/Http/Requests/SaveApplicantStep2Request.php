<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveApplicantStep2Request extends FormRequest
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
            'junior_school' => 'required|string|max:190',
            'senior_school' => 'required_unless:no_k12,1|nullable|string|max:190',
            'shs_track_strand' => 'required|string|max:120',
            'no_k12' => 'nullable|boolean',
            'learner_reference_number' => 'required|digits:12',
        ];
    }

    public function messages()
    {
        return [
            'junior_school.required' => 'Junior school is required.',
            'senior_school.required_unless' => 'Senior school is required unless No K-12 is enabled.',
            'shs_track_strand.required' => 'SHS track strand is required.',
            'learner_reference_number.required' => 'Learner reference number (LRN) is required.',
            'learner_reference_number.digits' => 'Learner reference number (LRN) must be exactly 12 digits.',
        ];
    }
}
