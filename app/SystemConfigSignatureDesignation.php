<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SystemConfigSignatureDesignation extends Model
{
    protected $fillable = [
        'code',
        'name',
        'sort_order',
    ];

    public function signatures()
    {
        return $this->hasMany(SystemConfigNameSignature::class, 'designation_id');
    }
}
