<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomBuildingRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    protected function prepareForValidation()
    {
        $name = preg_replace('/\s+/', ' ', trim((string) $this->input('name')));

        $this->merge([
            'name' => $name === null ? '' : $name,
        ]);
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:120',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Building name is required.',
        ];
    }
}
