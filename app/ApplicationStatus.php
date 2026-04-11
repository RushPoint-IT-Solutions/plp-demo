<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApplicationStatus extends Model
{
    protected $fillable = [
        'status_code',
        'status_name',
        'status_message',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
