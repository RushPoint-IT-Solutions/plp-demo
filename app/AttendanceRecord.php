<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    protected $fillable = [
        'subject_id',
        'student_id',
        'attendance_date',
        'status',
        'remarks',
        'recorded_by_user_id',
    ];

    protected $casts = [
        'attendance_date' => 'date',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
