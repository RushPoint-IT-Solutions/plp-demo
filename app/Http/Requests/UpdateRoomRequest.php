<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoomRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        $roomRoute = $this->route('room');
        $roomId = is_object($roomRoute) ? $roomRoute->id : $roomRoute;

        return [
            'room_number' => [
                'required',
                'integer',
                'min:1',
                'max:9999',
                Rule::unique('rooms', 'room_number')
                    ->where(function ($query) {
                        $query->where('room_hallway_id', (int) $this->input('room_hallway_id'))
                            ->where('floor_number', (int) $this->input('floor_number'));
                    })
                    ->ignore((int) $roomId),
            ],
            'floor_number' => 'required|integer|min:1|max:99',
            'capacity' => 'required|integer|min:1|max:999',
            'room_building_id' => 'required|integer|exists:room_buildings,id',
            'room_hallway_id' => [
                'required',
                'integer',
                Rule::exists('room_hallways', 'id')->where(function ($query) {
                    $query->where('room_building_id', (int) $this->input('room_building_id'));
                }),
            ],
            'course_ids' => 'nullable|array|max:20',
            'course_ids.*' => 'nullable|integer|distinct|exists:courses,id',
            'subject_ids' => 'required|array|min:1|max:100',
            'subject_ids.*' => 'required|integer|distinct|exists:subjects,id',
        ];
    }

    public function messages()
    {
        return [
            'room_number.unique' => 'The room number already exists for the selected floor and hallway.',
            'room_hallway_id.exists' => 'The selected hallway is invalid for the selected building.',
        ];
    }
}
