<?php

namespace App;

// app/Subject.php
use App\Concerns\ResolvesProgramYearDimensions;
use App\Concerns\ResolvesLookupCodeFields;
use App\Concerns\ResolvesAcademicTerm;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use ResolvesAcademicTerm, ResolvesProgramYearDimensions, ResolvesLookupCodeFields;

    protected $fillable = [
        'code', 'name', 'units', 'days',
        'is_subject_file_record',
        'lec', 'lab',
        'is_core', 'is_applied', 'is_specialized',
        'hours', 'course_type',
        'required_room_type', 'required_equipment', 'max_class_size',
        'required_lecture_room_type', 'required_laboratory_room_type', 'room_requirement_status',
        'time_start', 'time_end', 'room', 'faculty',
        'faculty_id',
        'year_section', 'course', 'course_id', 'semester', 'school_year', 'academic_term_id',
        'grading_status', 'grading_status_id',
        'submitted_at', 'dean_approved_by', 'dean_approved_at', 'dean_approved_by_name',
        'registrar_finalized_by', 'registrar_finalized_at', 'registrar_finalized_by_name',
        'grading_returned_by', 'grading_returned_at', 'grading_return_reason',
        'load_type', 'load_type_id', 'credited_tuition_units', 'load_hours', 'added_by',
    ];

    protected $casts = [
        'is_subject_file_record' => 'boolean',
        'is_core' => 'boolean',
        'is_applied' => 'boolean',
        'is_specialized' => 'boolean',
        'submitted_at' => 'datetime',
        'dean_approved_at' => 'datetime',
        'registrar_finalized_at' => 'datetime',
        'grading_returned_at' => 'datetime',
    ];

        public function curriculumAssignments()
        {
            return $this->hasMany(CourseCurriculumSubject::class);
        }

        public function requisiteLinks()
        {
            return $this->hasMany(CurriculumSubjectRequisite::class, 'requisite_subject_id');
        }

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

    public function studentGrades()
    {
        return $this->hasMany(StudentSubjectGrade::class);
    }

    public function facultyModel()
    {
        return $this->belongsTo(Faculty::class, 'faculty_id');
    }

    public function canonicalCourse()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function allowedRooms()
    {
        return $this->belongsToMany(Room::class, 'room_allowed_subjects', 'subject_id', 'room_id')
            ->withPivot('assigned_by_user_id')
            ->withTimestamps();
    }

    public function qualifiedFaculty()
    {
        return $this->belongsToMany(Faculty::class, 'teacher_allowed_subjects', 'subject_id', 'faculty_id')
            ->withPivot('assigned_by_user_id')
            ->withTimestamps();
    }

    public function gradingStatusLookup()
    {
        return $this->belongsTo(SubjectGradingStatus::class, 'grading_status_id');
    }

    public function loadTypeLookup()
    {
        return $this->belongsTo(SubjectLoadType::class, 'load_type_id');
    }

    public function getGradingStatusAttribute($value)
    {
        return $this->getLookupCodeAttributeValue('grading_status', 'gradingStatusLookup', $value);
    }

    public function setGradingStatusAttribute($value)
    {
        $this->setLookupCodeAttributeValue('grading_status', 'grading_status_id', SubjectGradingStatus::class, $value);
    }

    public function getLoadTypeAttribute($value)
    {
        return $this->getLookupCodeAttributeValue('load_type', 'loadTypeLookup', $value);
    }

    public function setLoadTypeAttribute($value)
    {
        $this->setLookupCodeAttributeValue('load_type', 'load_type_id', SubjectLoadType::class, $value);
    }
}
