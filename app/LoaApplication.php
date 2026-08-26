<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoaApplication extends Model
{
    public const TYPE_ENROLLED = 'enrolled';
    public const TYPE_NON_ENROLLED = 'non_enrolled';
    public const TYPE_LATE = 'late';

    public const FILING_TYPES = [
        self::TYPE_ENROLLED => 'Enrolled',
        self::TYPE_NON_ENROLLED => 'Non-Enrolled',
        self::TYPE_LATE => 'Late Filing',
    ];

    public const REASONS = [
        'Medical Condition',
        'Financial Constraint',
        'Unavailability of Subject',
        'Work',
        'Pregnancy',
        'Family Problem',
        'Other',
    ];

    protected $fillable = [
        'student_id',
        'academic_term_id',
        'course_id',
        'college_id',
        'department_id',
        'school_year',
        'semester',
        'filing_type',
        'reason',
        'reason_details',
        'program',
        'college_department',
        'application_date',
        'status',
        'recorded_by_user_id',
    ];

    protected $casts = [
        'application_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function academicTerm()
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    public function getFilingTypeLabelAttribute(): string
    {
        return self::FILING_TYPES[$this->filing_type] ?? ucfirst(str_replace('_', ' ', (string) $this->filing_type));
    }
}
