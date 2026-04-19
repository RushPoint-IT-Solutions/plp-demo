<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ParentContactRequestStatus extends Model
{
    protected $fillable = [
        'code',
        'name',
        'sort_order',
        'is_terminal',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_terminal' => 'boolean',
    ];

    public function requests()
    {
        return $this->hasMany(ParentContactRequest::class, 'status_id');
    }
}
