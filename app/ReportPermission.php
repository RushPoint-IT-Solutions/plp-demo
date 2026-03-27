<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReportPermission extends Model
{
    protected $fillable = [
        'user_id',
        'report_key',
        'report_type',
        'is_allowed',
    ];

    protected $casts = [
        'is_allowed' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
