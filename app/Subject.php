<?php

namespace App;

// app/Subject.php
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        'code', 'name', 'units', 'days',
        'time_start', 'time_end', 'room', 'faculty',
        'semester', 'school_year',
    ];

    /**
     * Formatted time range: "04:00PM–07:00PM"
     */
    public function getTimeRangeAttribute()
    {
        return $this->time_start . '–' . $this->time_end;
    }

    /**
     * The students enrolled in this subject.
     */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_subject');
    }
}
