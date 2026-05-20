<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    protected $fillable = [
        'code',
        'name',
        'department',
        'department_id',
        'college_id',
        'employment_type',
        'max_load_units',
    ];

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    public function qualifiedSubjects()
    {
        return $this->belongsToMany(Subject::class, 'teacher_allowed_subjects', 'faculty_id', 'subject_id')
            ->withPivot('assigned_by_user_id')
            ->withTimestamps();
    }

    public function departmentLookup()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function college()
    {
        return $this->belongsTo(College::class);
    }
}
