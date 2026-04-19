<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ParentContactRequestChannel extends Model
{
    protected $fillable = [
        'code',
        'name',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function requests()
    {
        return $this->hasMany(ParentContactRequest::class, 'channel_id');
    }
}
