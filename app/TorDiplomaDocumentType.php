<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TorDiplomaDocumentType extends Model
{
    protected $fillable = [
        'code',
        'name',
    ];

    public function documents()
    {
        return $this->hasMany(TorDiplomaDocument::class, 'document_type_id');
    }

    public function footerConfigs()
    {
        return $this->hasMany(TorDiplomaFooterConfig::class, 'document_type_id');
    }
}
