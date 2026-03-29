<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StudentDeficiency extends Model
{
    protected $fillable = [
        'student_id',
        'department',
        'remarks',
        'date_today',
        'submission_date',
        'is_completed',
        'compliance_date',
        'updated_by',
    ];

    protected $casts = [
        'date_today' => 'date',
        'submission_date' => 'date',
        'compliance_date' => 'date',
        'is_completed' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
