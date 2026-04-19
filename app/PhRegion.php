<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PhRegion extends Model
{
    protected $table = 'ph_regions';

    protected $fillable = [
        'psgc_code',
        'region_name',
        'region_code',
    ];

    public function provinces()
    {
        return $this->hasMany(PhProvince::class, 'region_id');
    }
}
