<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSectionMergingRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    protected function prepareForValidation()
    {
        $schoolYear = preg_replace('/\s+/', ' ', trim((string) $this->input('school_year')));
        $semester = preg_replace('/\s+/', ' ', trim((string) $this->input('semester')));

        $this->merge([
            'school_year' => $schoolYear === null ? '' : $schoolYear,
            'semester' => $semester === null ? '' : $semester,
            'source_slot_monitoring_id' => (int) $this->input('source_slot_monitoring_id', 0),
            'target_slot_monitoring_id' => (int) $this->input('target_slot_monitoring_id', 0),
        ]);
    }

    public function rules()
    {
        return [
            'school_year' => 'required|string|max:20',
            'semester' => 'required|in:First,Second,Summer',
            'source_slot_monitoring_id' => 'required|integer|min:1|exists:slot_monitorings,id',
            'target_slot_monitoring_id' => 'required|integer|min:1|exists:slot_monitorings,id|different:source_slot_monitoring_id',
        ];
    }

    public function messages()
    {
        return [
            'target_slot_monitoring_id.different' => 'Source and target sections must be different.',
        ];
    }
}
