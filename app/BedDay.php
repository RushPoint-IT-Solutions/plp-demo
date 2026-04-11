<?php

namespace App;

use App\Concerns\ResolvesAcademicTerm;
use Illuminate\Database\Eloquent\Model;

class BedDay extends Model
{
    use ResolvesAcademicTerm;

    protected $fillable = [
        'school_year',
        'semester',
        'academic_term_id',
        'month_name',
        'number_of_days',
    ];
}
