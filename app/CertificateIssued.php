<?php

namespace App;

use App\Concerns\ResolvesAcademicTerm;
use App\Concerns\ResolvesLookupCodeFields;
use Illuminate\Database\Eloquent\Model;

class CertificateIssued extends Model
{
    use ResolvesAcademicTerm, ResolvesLookupCodeFields;

    protected $table = 'certificates_issued';

    protected $fillable = [
        'student_id',
        'certificate_type',
        'certificate_type_id',
        'purpose',
        'date_issued',
        'issued_by',
        'school_year',
        'semester',
        'academic_term_id',
    ];

    protected $casts = [
        'date_issued' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function certificateTypeLookup()
    {
        return $this->belongsTo(CertificateType::class, 'certificate_type_id');
    }

    public function getCertificateTypeAttribute($value)
    {
        return $this->getLookupCodeAttributeValue('certificate_type', 'certificateTypeLookup', $value);
    }

    public function setCertificateTypeAttribute($value)
    {
        $this->setLookupCodeAttributeValue('certificate_type', 'certificate_type_id', CertificateType::class, $value);
    }
}
