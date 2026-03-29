<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterFacultyFile extends Model
{
    protected $table = 'master_faculty_files';

    protected $fillable = [
        'code',
        'name',
        'department',
        'status',
        'config_payload',
    ];

    protected $casts = [
        'config_payload' => 'array',
    ];
}
