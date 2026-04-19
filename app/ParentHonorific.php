<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ParentHonorific extends Model
{
    protected $fillable = [
        'code',
        'name',
    ];

    public function parents()
    {
        return $this->hasMany(ParentAccount::class, 'honorific_id');
    }
}
