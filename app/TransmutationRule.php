<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransmutationRule extends Model
{
    protected $fillable = [
        'school_year',
        'term',
        'program',
        'initial_from',
        'initial_to',
        'transmuted_grade',
        'code',
        'remarks',
    ];
}
