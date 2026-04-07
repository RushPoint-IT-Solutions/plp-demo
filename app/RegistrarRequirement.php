<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RegistrarRequirement extends Model
{
    protected $fillable = [
        'registrar_requirement_policy_id',
        'year_block_id',
        'applies_to_all_year_levels',
        'requirement_name',
        'requirement_type',
        'non_filipino',
        'created_by_user_id',
    ];

    protected $casts = [
        'applies_to_all_year_levels' => 'boolean',
        'non_filipino' => 'boolean',
    ];

    public function yearBlock()
    {
        return $this->belongsTo(YearBlock::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function policy()
    {
        return $this->belongsTo(RegistrarRequirementPolicy::class, 'registrar_requirement_policy_id');
    }
}
