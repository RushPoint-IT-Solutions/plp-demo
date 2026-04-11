<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CurriculumRequisiteType extends Model
{
    protected $fillable = [
        'code',
        'name',
    ];

    public function subjectRequisites()
    {
        return $this->hasMany(CurriculumSubjectRequisite::class);
    }
}
