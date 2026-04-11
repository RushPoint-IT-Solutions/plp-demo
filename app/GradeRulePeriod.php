<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GradeRulePeriod extends Model
{
    protected $fillable = [
        'grade_rule_id',
        'period_name',
        'sort_order',
    ];

    public function gradeRule()
    {
        return $this->belongsTo(GradeRule::class);
    }
}
