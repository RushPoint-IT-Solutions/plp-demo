<?php

namespace App;

use App\Concerns\ResolvesAcademicTerm;
use Illuminate\Database\Eloquent\Model;

class SystemIncProcessRun extends Model
{
    use ResolvesAcademicTerm;

    protected $fillable = [
        'academic_term_id',
        'school_year',
        'semester',
        'triggered_by_user_id',
        'processed_count',
        'notes',
    ];
}
