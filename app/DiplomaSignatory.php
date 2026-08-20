<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DiplomaSignatory extends Model
{
    protected $fillable = [
        'student_no',
        'copy_type',
        'registrar_name',
        'registrar_title',
        'president_name',
        'president_title',
        'chairman_name',
        'chairman_title',
    ];
}
