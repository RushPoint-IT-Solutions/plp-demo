<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'room_code',
        'room_name',
        'room_hallway_id',
        'room_number',
        'floor_number',
        'capacity',
        'room_type',
        'available_days',
        'available_start_time',
        'available_end_time',
        'status',
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

    public function allowedSubjects()
    {
        return $this->belongsToMany(Subject::class, 'room_allowed_subjects', 'room_id', 'subject_id')
            ->withPivot('assigned_by_user_id')
            ->withTimestamps();
    }
}
