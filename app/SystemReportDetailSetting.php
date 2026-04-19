<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SystemReportDetailSetting extends Model
{
    protected $fillable = [
        'region',
        'division',
        'school_id',
        'school_name',
        'contact_details',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
