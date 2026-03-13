<?php

namespace App;

// app/Subject.php
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        'code', 'name', 'units', 'days',
        'lec', 'lab',
        'time_start', 'time_end', 'room', 'faculty',
        'faculty_id',
        'year_section', 'course', 'semester', 'school_year',
        'grading_status',
        'load_type', 'credited_tuition_units', 'load_hours', 'added_by',
    ];

    /**
     * Formatted time range for display: "01:00-02:00 PM"
     */
    public function getTimeRangeAttribute()
    {
        return $this->time_start . '–' . $this->time_end;
    }

    /**
     * Formatted time range for display: "01:00-02:00 PM"
     * Strips duplicate AM/PM marker from start time.
     */
    public function getFormattedTimeAttribute()
    {
        $start = preg_replace('/(AM|PM)$/i', '', $this->time_start ?? '');
        $end   = $this->time_end ?? '';
        preg_match('/(AM|PM)$/i', $end, $m);
        $period  = isset($m[1]) ? strtoupper($m[1]) : '';
        $endTime = preg_replace('/(AM|PM)$/i', '', $end);
        return $start . '-' . $endTime . ' ' . $period;
    }

    /**
     * The students enrolled in this subject.
     */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_subject');
    }

    /**
     * Faculty evaluations for this subject.
     */
    public function evaluations()
    {
        return $this->hasMany(FacultyEvaluation::class);
    }

    public function facultyModel()
    {
        return $this->belongsTo(Faculty::class, 'faculty_id');
    }
}
