<?php

namespace App\Http\Requests;

use App\Course;
use Illuminate\Foundation\Http\FormRequest;

class SaveApplicantStep4Request extends FormRequest
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
