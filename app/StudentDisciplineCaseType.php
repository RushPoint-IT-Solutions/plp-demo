<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StudentDisciplineCaseType extends Model
{
    protected $fillable = [
        'code',
        'label',
    ];

    public function records()
    {
        return $this->hasMany(StudentDisciplineRecord::class, 'case_type_id');
    }
}
