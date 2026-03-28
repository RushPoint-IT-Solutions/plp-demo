<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserAccountStatus extends Model
{
    protected $fillable = [
        'user_id',
        'is_inactive',
    ];

    protected $casts = [
        'is_inactive' => 'boolean',
    ];
}
