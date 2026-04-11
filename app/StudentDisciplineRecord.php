<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StudentDisciplineRecord extends Model
{
    protected $fillable = [
        'discipline_student_id',
        'case_type_id',
        'action_type_id',
        'incident_date',
        'walk_in',
        'called_by',
        'description',
        'action_date',
        'counselor',
        'remarks',
        'is_completed',
        'updated_by',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'action_date' => 'date',
        'walk_in' => 'boolean',
        'is_completed' => 'boolean',
    ];

    public function disciplineStudent()
    {
        return $this->belongsTo(StudentDisciplineStudent::class, 'discipline_student_id');
    }

    public function caseType()
    {
        return $this->belongsTo(StudentDisciplineCaseType::class, 'case_type_id');
    }

    public function actionType()
    {
        return $this->belongsTo(StudentDisciplineActionType::class, 'action_type_id');
    }
}
