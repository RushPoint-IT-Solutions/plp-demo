<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RoomCourseAssignment extends Model
{
    protected $table = 'room_course_assignments';

    protected $fillable = [
        'room_id',
        'course_id',
        'assigned_by_user_id',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }
}
