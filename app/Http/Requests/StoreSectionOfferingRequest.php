<?php

namespace App\Http\Requests;

use App\Support\SystemConfigSchoolTermOptions;
use Illuminate\Foundation\Http\FormRequest;

class StoreSectionOfferingRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    protected function prepareForValidation()
    {
        $schoolYear = preg_replace('/\s+/', '', (string) $this->input('school_year'));
        $semester = preg_replace('/\s+/', ' ', trim((string) $this->input('semester')));
        $yearLevel = preg_replace('/\s+/', ' ', trim((string) $this->input('year_level')));
        $section = preg_replace('/\s+/', ' ', trim((string) $this->input('section')));
        $adviser = preg_replace('/\s+/', ' ', trim((string) $this->input('adviser')));
        $description = preg_replace('/\s+/', ' ', trim((string) $this->input('description')));

        $curriculumIds = collect((array) $this->input('curriculum_subject_ids', []))
            ->map(function ($value) {
                return (int) $value;
            })
            ->filter(function ($value) {
                return $value > 0;
            })
            ->unique()
            ->values()
            ->all();

        $this->merge([
            'school_year' => $schoolYear,
            'semester' => $semester,
            'year_level' => $yearLevel,
            'section' => $section,
            'adviser' => $adviser,
            'description' => $description,
            'curriculum_subject_ids' => $curriculumIds,
        ]);
    }

    public function rules()
    {
        $configOptions = SystemConfigSchoolTermOptions::resolveOptions();
        $schoolYearOptions = array_values($configOptions['school_years'] ?? []);

        return [
            'course_id' => 'required|integer|exists:courses,id',
            'school_year' => ['required', 'string', 'max:20', 'in:' . implode(',', $schoolYearOptions)],
            'semester' => 'required|string|in:First,Second,Summer,1st Semester,2nd Semester,First Semester,Second Semester,Summer Semester',
            'year_level' => 'required|string|in:First,Second,Third,Fourth,Fifth,Sixth,1,2,3,4,5,6',
            'section' => 'required|string|max:24',
            'slots' => 'nullable|integer|min:1|max:999',
            'adviser' => 'nullable|string|max:120',
            'description' => 'nullable|string|max:255',
            'curriculum_subject_ids' => 'required|array|min:1',
            'curriculum_subject_ids.*' => 'integer|exists:course_curriculum_subjects,id',
        ];
    }

    public function messages()
    {
        return [
            'school_year.in' => 'Select a valid School Year from the list.',
            'curriculum_subject_ids.min' => 'Select at least one curriculum subject.',
        ];
    }
}
