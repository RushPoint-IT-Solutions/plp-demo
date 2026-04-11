<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PortalNotification extends Model
{
    protected $fillable = [
        'notification_type_id',
        'title',
        'message',
        'source_module',
        'source_reference',
        'source_url',
        'created_by_user_id',
    ];

    public function type()
    {
        return $this->belongsTo(NotificationType::class, 'notification_type_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function deliveries()
    {
        return $this->hasMany(NotificationDelivery::class);
    }

    public function getLocalSourceUrlAttribute()
    {
        $sourceUrl = trim((string) $this->source_url);
        if ($sourceUrl === '') {
            return '';
        }

        $parts = parse_url($sourceUrl);
        if ($parts === false) {
            return $sourceUrl;
        }

        $isAbsolute = isset($parts['scheme']) || isset($parts['host']);
        if (!$isAbsolute) {
            return $sourceUrl;
        }

        $path = isset($parts['path']) && $parts['path'] !== '' ? $parts['path'] : '/';
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        return $path . $query . $fragment;
    }
}
