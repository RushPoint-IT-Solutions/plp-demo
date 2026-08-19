<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScholarshipStudent extends Model
{
    protected $table = 'scholarship_student';

    protected $fillable = [
        'student_id',
        'scholarship_program_id',
        'is_unifast',
        'school_year',
        'semester',
        'application_status',
        'evaluation_status',
        'approval_status',
        'award_status',
        'renewal_status',
        'monitoring_status',
        'financial_posting_status',
        'posted_amount',
        'discount_percent',
        'current_gwa',
        'application_date',
        'approval_date',
        'renewal_due_date',
        'remarks',
        'tagged_by_user_id',
    ];

    protected $casts = [
        'is_unifast' => 'boolean',
        'posted_amount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'current_gwa' => 'decimal:2',
        'application_date' => 'date',
        'approval_date' => 'date',
        'renewal_due_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function program()
    {
        return $this->belongsTo(ScholarshipProgram::class, 'scholarship_program_id');
    }
}
