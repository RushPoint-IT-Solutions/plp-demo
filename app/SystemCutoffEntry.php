<?php

namespace App;

use App\Concerns\ResolvesAcademicTerm;
use Illuminate\Database\Eloquent\Model;

class SystemCutoffEntry extends Model
{
    use ResolvesAcademicTerm;

    protected $fillable = [
        'cutoff_type_id',
        'academic_term_id',
        'school_year',
        'semester',
        'student_no',
        'event_date',
        'cutoff_date',
        'notes',
    ];

    protected $casts = [
        'event_date' => 'date',
        'cutoff_date' => 'date',
    ];

    public function cutoffType()
    {
        return $this->belongsTo(SystemCutoffType::class, 'cutoff_type_id');
    }
}
