<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class College extends Model
{
    protected $fillable = [
        'code',
        'name',
        'abbr',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function faculties()
    {
        return $this->hasMany(Faculty::class);
    }

    public function applicants()
    {
        return $this->hasMany(Applicant::class);
    }
}
