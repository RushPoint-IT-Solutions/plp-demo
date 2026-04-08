<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'code',
        'name',
        'program_type',
        'department_id',
        'description',
        'program_file',
        'slots',
        'track_category',
        'non_filipino',
        'dean_director_id',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function deanDirector()
    {
        return $this->belongsTo(Faculty::class, 'dean_director_id');
    }
}
