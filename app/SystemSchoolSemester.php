<?php

namespace App;

use App\Concerns\ResolvesAcademicTerm;
use Illuminate\Database\Eloquent\Model;

class SystemSchoolSemester extends Model
{
    use ResolvesAcademicTerm;

    protected $fillable = [
        'school_year',
        'semester',
        'academic_term_id',
    ];

    public function registrarRequirementPolicies()
    {
        return $this->hasMany(RegistrarRequirementPolicy::class);
    }
}
