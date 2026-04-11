<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotificationDelivery extends Model
{
    protected $fillable = [
        'portal_notification_id',
        'user_id',
        'delivered_at',
        'read_at',
        'dismissed_at',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'dismissed_at' => 'datetime',
    ];

    public function notification()
    {
        return $this->belongsTo(PortalNotification::class, 'portal_notification_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
