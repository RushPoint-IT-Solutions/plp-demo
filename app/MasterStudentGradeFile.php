<?php

namespace App;

use App\Concerns\ResolvesProgramYearDimensions;
use Illuminate\Database\Eloquent\Model;

class MasterStudentGradeFile extends Model
{
    use ResolvesProgramYearDimensions;

    protected $table = 'master_student_grade_files';

    protected $fillable = [
        'student_no',
        'source_student_id',
        'student_name',
        'course',
        'year_level',
        'course_id',
        'year_block_id',
        'snapshot_taken_at',
        'snapshot_note',
        'is_snapshot',
    ];

    protected $casts = [
        'snapshot_taken_at' => 'datetime',
        'is_snapshot' => 'boolean',
    ];

    public function sourceStudent()
    {
        return $this->belongsTo(Student::class, 'source_student_id');
    }

    public function canonicalCourse()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function yearBlock()
    {
        return $this->belongsTo(YearBlock::class, 'year_block_id');
    }
}
