<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'room_hallway_id',
        'room_number',
        'floor_number',
        'capacity',
        'updated_by_user_id',
    ];

    public function hallway()
    {
        return $this->belongsTo(RoomHallway::class, 'room_hallway_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'room_course_assignments', 'room_id', 'course_id')
            ->withPivot('assigned_by_user_id')
            ->withTimestamps();
    }
}
