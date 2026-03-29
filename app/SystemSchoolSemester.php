<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SystemSchoolSemester extends Model
{
    protected $fillable = [
        'school_year',
        'semester',
    ];
}
