<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomHallwayRequest extends FormRequest
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
            'room_building_id' => 'required|integer|exists:room_buildings,id',
            'name' => 'required|string|max:120',
        ];
    }

    public function messages()
    {
        return [
            'room_building_id.required' => 'Please select a building before adding a hallway.',
            'room_building_id.exists' => 'The selected building is invalid.',
            'name.required' => 'Hallway name is required.',
        ];
    }
}
