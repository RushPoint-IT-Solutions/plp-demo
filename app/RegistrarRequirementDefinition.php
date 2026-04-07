<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RegistrarRequirementDefinition extends Model
{
    protected $fillable = [
        'requirement_name',
        'registrar_requirement_type_id',
        'non_filipino_only',
        'created_by_user_id',
    ];

    protected $casts = [
        'non_filipino_only' => 'boolean',
    ];

    public function type()
    {
        return $this->belongsTo(RegistrarRequirementType::class, 'registrar_requirement_type_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function policies()
    {
        return $this->hasMany(RegistrarRequirementPolicy::class, 'registrar_requirement_definition_id');
    }
}
