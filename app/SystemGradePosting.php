<?php

namespace App;

use App\Concerns\ResolvesAcademicTerm;
use Illuminate\Database\Eloquent\Model;

class SystemGradePosting extends Model
{
    use ResolvesAcademicTerm;

    protected $fillable = [
        'school_year',
        'semester',
        'academic_term_id',
        'period',
        'date_from',
    ];

    protected $casts = [
        'date_from' => 'date',
    ];
}
