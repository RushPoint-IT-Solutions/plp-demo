<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StudentMedicalRecord extends Model
{
    protected $table = 'student_medical_records';

    protected $fillable = [
        'student_id',
        'blood_type',
        'height_cm',
        'weight_kg',
        'allergies',
        'medical_conditions',
        'current_medications',
        'past_illnesses',
        'immunization_status',
        'immunization_notes',
        'vision_od',
        'vision_os',
        'hearing_right',
        'hearing_left',
        'dental_status',
        'mental_health_notes',
        'emergency_contact_name',
        'emergency_contact_number',
        'emergency_contact_relation',
        'last_physical_exam_date',
        'exam_findings',
        'physician_name',
        'remarks',
        'recorded_by',
    ];

    protected $casts = [
        'last_physical_exam_date' => 'date',
        'height_cm'               => 'float',
        'weight_kg'               => 'float',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function getBmiAttribute()
    {
        if ($this->height_cm > 0 && $this->weight_kg > 0) {
            $heightM = $this->height_cm / 100;
            return round($this->weight_kg / ($heightM * $heightM), 1);
        }
        return null;
    }

    public function getBmiCategoryAttribute()
    {
        $bmi = $this->bmi;
        if ($bmi === null) { return null; }
        if ($bmi < 18.5) { return 'Underweight'; }
        if ($bmi < 25.0) { return 'Normal'; }
        if ($bmi < 30.0) { return 'Overweight'; }
        return 'Obese';
    }
}
