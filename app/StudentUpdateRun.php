<?php

namespace App;

use App\Concerns\ResolvesAcademicTerm;
use App\Concerns\ResolvesProgramYearDimensions;
use Illuminate\Database\Eloquent\Model;

class StudentUpdateRun extends Model
{
    use ResolvesAcademicTerm, ResolvesProgramYearDimensions;

    protected $fillable = [
        'action_name',
        'run_mode',
        'school_year',
        'term',
        'academic_term_id',
        'period',
        'operator',
        'course',
        'year_level',
        'course_id',
        'year_block_id',
        'section',
        'student_no',
        'include_unpaid_only',
        'active_only',
        'affected_count',
    ];

    protected $casts = [
        'include_unpaid_only' => 'boolean',
        'active_only' => 'boolean',
    ];

    public function canonicalCourse()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function yearBlock()
    {
        return $this->belongsTo(YearBlock::class, 'year_block_id');
    }
}
