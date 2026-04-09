<?php

namespace App;

use App\Concerns\ResolvesAcademicTerm;
use Illuminate\Database\Eloquent\Model;

class AlumniTrackerSetting extends Model
{
    use ResolvesAcademicTerm;

    protected $fillable = [
        'school_year',
        'term',
        'academic_term_id',
    ];
}
