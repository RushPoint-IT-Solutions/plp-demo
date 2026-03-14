<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'code',
        'description',
    ];

    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}
