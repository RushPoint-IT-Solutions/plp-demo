<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotificationType extends Model
{
    protected $fillable = [
        'code',
        'name',
    ];

    public function notifications()
    {
        return $this->hasMany(PortalNotification::class);
    }
}
