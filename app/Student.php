<?php

namespace App;

// app/Student.php
use App\Concerns\ResolvesAcademicTerm;
use App\Concerns\ResolvesProgramYearDimensions;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use ResolvesAcademicTerm, ResolvesProgramYearDimensions;

    protected $fillable = [
        'student_no', 'name', 'sex', 'age',
        'college', 'program', 'curriculum', 'year_level',
        'scholarship', 'registration_no', 'school_year', 'semester', 'academic_term_id',
        'course_id', 'year_block_id',
        'is_withdrawn', 'withdrawn_date', 'withdrawn_remarks',
    ];

    protected $casts = [
        'is_withdrawn' => 'boolean',
        'withdrawn_date' => 'date',
    ];

    /**
     * Scope: only active (non-withdrawn) students.
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('is_withdrawn')->orWhere('is_withdrawn', false);
        });
    }

    /**
     * Generate the next available student number for the given year.
     * Format: PLP-YYYY-NNNNN (5-digit, resets each year, withdrawn numbers are never reused).
     */
    public static function generateStudentNo(int $year): string
    {
        $prefix = 'PLP-' . $year . '-';

        // Find the highest sequence used this year across ALL students (including withdrawn)
        $last = static::where('student_no', 'like', $prefix . '%')
            ->orderByDesc('student_no')
            ->value('student_no');

        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    /**
     * The subjects this student is enrolled in.
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'student_subject');
    }

    /**
     * Linked user account for student login.
     */
    public function user()
    {
        return $this->hasOne(User::class, 'student_id');
    }

    public function profile()
    {
        return $this->hasOne(StudentProfile::class, 'student_id');
    }

    /**
     * Deficiency records linked to the student.
     */
    public function deficiencies()
    {
        return $this->hasMany(StudentDeficiency::class);
    }

    public function canonicalCourse()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function yearBlock()
    {
        return $this->belongsTo(YearBlock::class, 'year_block_id');
    }

    public function requirementStatuses()
    {
        return $this->hasMany(StudentRequirementStatus::class);
    }

    public function graduateTagging()
    {
        return $this->hasOne(GraduateTagging::class, 'student_id');
    }

    public function scholarshipTags()
    {
        return $this->hasMany(ScholarshipStudent::class, 'student_id');
    }

    /**
     * Helper: formatted school year + semester label.
     * e.g. "SY 2025-2026 2nd Semester"
     */
    public function getSchoolYearLabelAttribute()
    {
        return 'SY ' . $this->school_year . ' ' . $this->semester;
    }
}
