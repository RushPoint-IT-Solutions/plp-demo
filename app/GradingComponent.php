<?php

namespace App;

use App\Concerns\ResolvesAcademicTerm;
use Illuminate\Database\Eloquent\Model;

class GradingComponent extends Model
{
    use ResolvesAcademicTerm;

    protected $fillable = [
        'school_year',
        'period',
        'semester',
        'academic_term_id',
        'section',
        'course_code',
        'title',
        'sequence_no',
        'percentage',
        'lab_mode',
        'cap',
        'updated_by',
        'effective_date',
    ];

    protected $casts = [
        'effective_date' => 'date',
    ];
}
