<?php

namespace App;

use App\Concerns\ResolvesAcademicTerm;
use App\Concerns\ResolvesProgramYearDimensions;
use Illuminate\Database\Eloquent\Model;

class TransmutationRule extends Model
{
    use ResolvesAcademicTerm, ResolvesProgramYearDimensions;

    protected $fillable = [
        'school_year',
        'term',
        'academic_term_id',
        'program',
        'course_id',
        'initial_from',
        'initial_to',
        'transmuted_grade',
        'code',
        'remarks',
    ];

    public function canonicalCourse()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
