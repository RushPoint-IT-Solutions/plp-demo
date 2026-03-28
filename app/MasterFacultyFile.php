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
    ];
}
