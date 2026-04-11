<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSlotMonitoringRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    protected function prepareForValidation()
    {
        $schoolYear = preg_replace('/\s+/', ' ', trim((string) $this->input('school_year')));
        $semester = preg_replace('/\s+/', ' ', trim((string) $this->input('semester')));
        $section = preg_replace('/\s+/', ' ', trim((string) $this->input('section')));
        $subject = preg_replace('/\s+/', ' ', trim((string) $this->input('subject')));
        $schedule = preg_replace('/\s+/', ' ', trim((string) $this->input('schedule')));

        $this->merge([
            'school_year' => $schoolYear === null ? '' : $schoolYear,
            'semester' => $semester === null ? '' : $semester,
            'section' => $section === null ? '' : $section,
            'subject' => $subject === null ? '' : $subject,
            'schedule' => $schedule === null ? '' : $schedule,
        ]);
    }

    public function rules()
    {
        return [
            'school_year' => 'required|string|max:20',
            'semester' => 'required|in:First,Second,Summer',
            'course_id' => 'required|integer|exists:courses,id',
            'section' => 'required|string|max:120',
            'subject' => [
                'required',
                'string',
                'max:150',
                Rule::unique('slot_monitorings', 'subject')->where(function ($query) {
                    $query->where('school_year', (string) $this->input('school_year'))
                        ->where('semester', (string) $this->input('semester'))
                        ->where('course_id', (int) $this->input('course_id'))
                        ->where('section', (string) $this->input('section'))
                        ->where('schedule', (string) $this->input('schedule'));
                }),
            ],
            'schedule' => 'required|string|max:190',
            'total_slots' => 'required|integer|min:1|max:999',
            'enrolled_slots' => 'nullable|integer|min:0|max:999|lte:total_slots',
        ];
    }

    public function messages()
    {
        return [
            'subject.unique' => 'A slot entry with the same school year, semester, course, section, subject, and schedule already exists.',
            'enrolled_slots.lte' => 'Enrolled slots cannot be greater than total slots.',
        ];
    }
}
