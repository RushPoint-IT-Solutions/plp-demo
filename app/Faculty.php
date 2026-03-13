<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    protected $fillable = [
        'code',
        'name',
    ];

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }
}
