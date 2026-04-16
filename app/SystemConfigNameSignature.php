<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SystemConfigNameSignature extends Model
{
    protected $fillable = [
        'designation_id',
        'signer_name',
        'signature_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function designation()
    {
        return $this->belongsTo(SystemConfigSignatureDesignation::class, 'designation_id');
    }
}
