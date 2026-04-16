<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SystemEmailSenderSetting extends Model
{
    protected $fillable = [
        'sender_email',
        'sender_password_encrypted',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
