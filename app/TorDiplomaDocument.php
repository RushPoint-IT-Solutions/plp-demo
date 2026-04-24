<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TorDiplomaDocument extends Model
{
    protected $fillable = [
        'student_id',
        'document_type_id',
        'control_number',
        'version_number',
        'is_current',
        'generated_by_user_id',
        'footer_text',
        'notes',
        'generated_at',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'is_current' => 'boolean',
        'generated_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function documentType()
    {
        return $this->belongsTo(TorDiplomaDocumentType::class, 'document_type_id');
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by_user_id');
    }

    public function amendments()
    {
        return $this->hasMany(TorDiplomaAmendment::class, 'tor_diploma_document_id');
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    public function scopeForType($query, string $typeCode)
    {
        return $query->whereHas('documentType', function ($q) use ($typeCode) {
            $q->where('code', $typeCode);
        });
    }

    /**
     * Generate a unique control number for a given document type.
     *
     * Format: TOR-2026-000001 or DPL-2026-000001
     */
    public static function generateControlNumber(string $typeCode): string
    {
        $prefix = strtoupper($typeCode) === 'DIPLOMA' || strtoupper($typeCode) === 'DPL'
            ? 'DPL'
            : 'TOR';

        $year = date('Y');
        $lastDoc = static::query()
            ->where('control_number', 'like', $prefix . '-' . $year . '-%')
            ->orderByDesc('id')
            ->first();

        $sequence = 1;
        if ($lastDoc) {
            $parts = explode('-', $lastDoc->control_number);
            $lastSequence = (int) end($parts);
            $sequence = $lastSequence + 1;
        }

        return sprintf('%s-%s-%06d', $prefix, $year, $sequence);
    }
}
