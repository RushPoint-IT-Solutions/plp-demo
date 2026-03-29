<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SystemGradePosting extends Model
{
    protected $fillable = [
        'school_year',
        'semester',
        'period',
        'date_from',
    ];

    protected $casts = [
        'date_from' => 'date',
    ];
}
