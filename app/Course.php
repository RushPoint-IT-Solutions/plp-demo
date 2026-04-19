<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'code',
        'name',
        'program_type',
        'college_id',
        'department_id',
        'description',
        'program_file',
        'slots',
        'track_category',
        'non_filipino',
        'dean_director_id',
    ];

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function deanDirector()
    {
        return $this->belongsTo(Faculty::class, 'dean_director_id');
    }

    public function curricula()
    {
        return $this->hasMany(CourseCurriculum::class);
    }

    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'room_course_assignments', 'course_id', 'room_id')
            ->withPivot('assigned_by_user_id')
            ->withTimestamps();
    }
}
