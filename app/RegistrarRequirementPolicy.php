<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RegistrarRequirementPolicy extends Model
{
    protected $fillable = [
        'registrar_requirement_definition_id',
        'system_school_semester_id',
        'year_block_id',
        'created_by_user_id',
    ];

    public function definition()
    {
        return $this->belongsTo(RegistrarRequirementDefinition::class, 'registrar_requirement_definition_id');
    }

    public function systemSchoolSemester()
    {
        return $this->belongsTo(SystemSchoolSemester::class);
    }

    public function yearBlock()
    {
        return $this->belongsTo(YearBlock::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function legacyRequirements()
    {
        return $this->hasMany(RegistrarRequirement::class, 'registrar_requirement_policy_id');
    }

    public function studentRequirementStatuses()
    {
        return $this->hasMany(StudentRequirementStatus::class, 'registrar_requirement_policy_id');
    }
}
