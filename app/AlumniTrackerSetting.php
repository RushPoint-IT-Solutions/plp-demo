<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AlumniTrackerSetting extends Model
{
    protected $fillable = [
        'school_year',
        'term',
    ];
}
