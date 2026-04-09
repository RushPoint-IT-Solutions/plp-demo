<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AcademicTerm extends Model
{
    protected $fillable = [
        'school_year',
        'term',
        'canonical_key',
    ];
}
