<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PhMunicipality extends Model
{
    protected $table = 'ph_municipalities';

    protected $fillable = [
        'psgc_code',
        'municipality_name',
        'province_id',
    ];

    public function province()
    {
        return $this->belongsTo(PhProvince::class, 'province_id');
    }
}
