<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SystemAnnouncement extends Model
{
    protected $fillable = [
        'date_from',
        'date_to',
        'title',
        'announcement_type',
        'program',
        'content',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
    ];
}
