<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'code',
        'description',
        'college_id',
    ];

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function faculties()
    {
        return $this->hasMany(Faculty::class, 'department_id');
    }
}
