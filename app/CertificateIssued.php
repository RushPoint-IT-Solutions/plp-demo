<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CertificateIssued extends Model
{
    protected $table = 'certificates_issued';

    protected $fillable = [
        'student_id',
        'certificate_type',
        'purpose',
        'date_issued',
        'issued_by',
        'school_year',
        'semester',
    ];

    protected $casts = [
        'date_issued' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
