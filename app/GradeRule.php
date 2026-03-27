<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GradeRule extends Model
{
    protected $fillable = [
        'code',
        'grade',
        'remarks',
        'periods',
    ];

    protected $casts = [
        'periods' => 'array',
    ];
}
