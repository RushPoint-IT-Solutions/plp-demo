<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RegistrarMessage extends Model
{
    protected $fillable = [
        'folder',
        'sender_name',
        'sender_type',
        'recipient',
        'subject',
        'body',
        'source_type',
        'source_id',
        'created_by_user_id',
        'read_at',
    ];

    protected $dates = [
        'read_at',
        'created_at',
        'updated_at',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
