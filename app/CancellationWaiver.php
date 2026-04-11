<?php

namespace App;

use App\Concerns\ResolvesAcademicTerm;
use App\Concerns\ResolvesLookupCodeFields;
use App\Concerns\ResolvesProgramYearDimensions;
use Illuminate\Database\Eloquent\Model;

class CancellationWaiver extends Model
{
    use ResolvesAcademicTerm, ResolvesProgramYearDimensions, ResolvesLookupCodeFields;

    protected $fillable = [
        'student_id',
        'school_year',
        'semester',
        'academic_term_id',
        'program',
        'year_level',
        'course_id',
        'year_block_id',
        'section',
        'status',
        'status_id',
        'requested_by',
        'approved_by',
        'remarks',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function canonicalCourse()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function yearBlock()
    {
        return $this->belongsTo(YearBlock::class, 'year_block_id');
    }

    public function statusLookup()
    {
        return $this->belongsTo(WaiverStatus::class, 'status_id');
    }

    public function getStatusAttribute($value)
    {
        return $this->getLookupCodeAttributeValue('status', 'statusLookup', $value);
    }

    public function setStatusAttribute($value)
    {
        $this->setLookupCodeAttributeValue('status', 'status_id', WaiverStatus::class, $value);
    }
}
