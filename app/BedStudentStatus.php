<?php

namespace App;

use App\Concerns\ResolvesAcademicTerm;
use App\Concerns\ResolvesProgramYearDimensions;
use Illuminate\Database\Eloquent\Model;

class BedStudentStatus extends Model
{
    use ResolvesAcademicTerm, ResolvesProgramYearDimensions;

    protected $fillable = [
        'student_no',
        'student_name',
        'course',
        'year_level',
        'section',
        'school_year',
        'term',
        'academic_term_id',
        'course_id',
        'year_block_id',
        'no_payment',
        'no_section',
    ];

    protected $casts = [
        'no_payment' => 'boolean',
        'no_section' => 'boolean',
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
