<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GradeCorrectionRequest extends Model
{
    protected $table = 'grade_correction_requests';

    protected $fillable = [
        'student_id',
        'student_no',
        'student_grade_record_id',
        'action',
        'status',
        'old_values',
        'new_values',
        'reason',
        'requested_by',
        'reviewed_by',
        'requested_at',
        'reviewed_at',
        'reviewer_remarks',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'requested_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function gradeRecord()
    {
        return $this->belongsTo(StudentGradeRecord::class, 'student_grade_record_id');
    }
}
