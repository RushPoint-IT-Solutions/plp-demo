<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StudentDisciplineActionType extends Model
{
    protected $fillable = [
        'code',
        'label',
    ];

    public function records()
    {
        return $this->hasMany(StudentDisciplineRecord::class, 'action_type_id');
    }
}
