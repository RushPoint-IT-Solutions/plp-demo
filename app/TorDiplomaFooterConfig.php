<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TorDiplomaFooterConfig extends Model
{
    protected $fillable = [
        'document_type_id',
        'key',
        'value',
        'updated_by_user_id',
    ];

    public function documentType()
    {
        return $this->belongsTo(TorDiplomaDocumentType::class, 'document_type_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    /**
     * Get all footer config values for a given document type code as a key-value array.
     */
    public static function configForType(string $typeCode): array
    {
        $type = TorDiplomaDocumentType::where('code', $typeCode)->first();

        if (!$type) {
            return [];
        }

        return static::where('document_type_id', $type->id)
            ->pluck('value', 'key')
            ->all();
    }
}
