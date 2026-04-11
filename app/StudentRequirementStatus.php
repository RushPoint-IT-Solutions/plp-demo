<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StudentRequirementStatus extends Model
{
    protected $fillable = [
        'student_id',
        'registrar_requirement_policy_id',
        'is_submitted',
        'remarks',
        'date_verified',
        'verified_by_user_id',
    ];

    protected $casts = [
        'is_submitted' => 'boolean',
        'date_verified' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function requirementPolicy()
    {
        return $this->belongsTo(RegistrarRequirementPolicy::class, 'registrar_requirement_policy_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }
}
