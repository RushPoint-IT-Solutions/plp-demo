<?php

namespace App;

use App\Concerns\ResolvesAcademicTerm;
use Illuminate\Database\Eloquent\Model;

class SystemCurriculumDisplaySetting extends Model
{
    use ResolvesAcademicTerm;

    protected $fillable = [
        'academic_term_id',
        'school_year',
        'semester',
        'display_status',
    ];
}
