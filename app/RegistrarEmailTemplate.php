<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RegistrarEmailTemplate extends Model
{
    protected $fillable = [
        'code',
        'name',
        'audience',
        'subject',
        'body',
        'is_active',
        'updated_by_user_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }
}
