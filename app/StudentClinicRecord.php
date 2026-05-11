<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StudentClinicRecord extends Model
{
    protected $table = 'student_clinic_records';

    protected $fillable = [
        'student_id',
        'visit_date',
        'chief_complaint',
        'diagnosis',
        'treatment',
        'medications_given',
        'temperature',
        'blood_pressure',
        'pulse_rate',
        'respiratory_rate',
        'weight_kg',
        'disposition',
        'referred_to',
        'attended_by',
        'remarks',
    ];

    protected $casts = [
        'visit_date'        => 'date',
        'temperature'       => 'float',
        'weight_kg'         => 'float',
        'pulse_rate'        => 'integer',
        'respiratory_rate'  => 'integer',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
