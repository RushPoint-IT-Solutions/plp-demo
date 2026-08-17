<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserCourseScope extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
