<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AcademicTerm extends Model
{
    protected $fillable = [
        'school_year',
        'term',
        'canonical_key',
        'status',
        'opened_at',
        'closed_at',
        'archived_at',
        'opened_by_user_id',
        'closed_by_user_id',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'archived_at' => 'datetime',
    ];
}
