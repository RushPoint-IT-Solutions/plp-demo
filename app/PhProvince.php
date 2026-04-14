<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PhProvince extends Model
{
    protected $table = 'ph_provinces';

    protected $fillable = [
        'psgc_code',
        'province_name',
        'region_id',
    ];

    public function region()
    {
        return $this->belongsTo(PhRegion::class, 'region_id');
    }

    public function municipalities()
    {
        return $this->hasMany(PhMunicipality::class, 'province_id');
    }
}
