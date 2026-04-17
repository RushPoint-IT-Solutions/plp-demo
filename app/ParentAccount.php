<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ParentAccount extends Model
{
    protected $table = 'parents';

    protected $fillable = [
        'parent_no',
        'honorific_id',
        'first_name',
        'last_name',
        'middle_name',
        'suffix',
        'email',
        'mobile_number',
    ];

    public function honorific()
    {
        return $this->belongsTo(ParentHonorific::class, 'honorific_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'parent_id');
    }

    public function studentLinks()
    {
        return $this->hasMany(ParentStudentLink::class, 'parent_id');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'parent_student_links', 'parent_id', 'student_id')
            ->withPivot(['relationship_type_id', 'is_primary_contact', 'receives_notifications'])
            ->withTimestamps();
    }

    public function getFullNameAttribute()
    {
        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
            $this->suffix,
        ])));
    }
}
