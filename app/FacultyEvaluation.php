<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FacultyEvaluation extends Model
{
    protected $fillable = ['subject_id', 'section', 'mean_score'];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
