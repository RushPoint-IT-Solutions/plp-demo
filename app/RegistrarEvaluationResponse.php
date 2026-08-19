<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RegistrarEvaluationResponse extends Model
{
    protected $fillable = [
        'evaluation_form_id',
        'answers',
        'submitted_at',
    ];

    protected $casts = [
        'answers' => 'array',
        'submitted_at' => 'datetime',
    ];

    public function form()
    {
        return $this->belongsTo(RegistrarEvaluationForm::class, 'evaluation_form_id');
    }
}
