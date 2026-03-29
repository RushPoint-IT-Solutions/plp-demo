<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BedDay extends Model
{
    protected $fillable = [
        'school_year',
        'semester',
        'month_name',
        'number_of_days',
    ];
}
