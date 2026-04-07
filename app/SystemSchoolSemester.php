<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SystemSchoolSemester extends Model
{
    protected $fillable = [
        'school_year',
        'semester',
    ];

    public function registrarRequirementPolicies()
    {
        return $this->hasMany(RegistrarRequirementPolicy::class);
    }
}
