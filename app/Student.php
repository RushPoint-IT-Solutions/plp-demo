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
    ];

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

    /**
     * Helper: formatted school year + semester label.
     * e.g. "SY 2025-2026 2nd Semester"
     */
    public function getSchoolYearLabelAttribute()
    {
        return 'SY ' . $this->school_year . ' ' . $this->semester;
    }
}
