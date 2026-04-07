<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RegistrarRequirementType extends Model
{
    protected $fillable = [
        'code',
        'name',
    ];

    public function definitions()
    {
        return $this->hasMany(RegistrarRequirementDefinition::class, 'registrar_requirement_type_id');
    }
}
