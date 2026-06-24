<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DocumentTemplate extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'content_json',
    ];

    protected $casts = [
        'content_json' => 'array',
    ];
}
