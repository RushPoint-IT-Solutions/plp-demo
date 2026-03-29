<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CrossEnrollmentRequest extends Model
{
    protected $fillable = [
        'student_id',
        'school_year',
        'semester',
        'program',
        'year_level',
        'section',
        'status',
        'requested_by',
        'approved_by',
        'remarks',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
