<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GraduateTagging extends Model
{
    protected $fillable = [
        'student_id',
        'is_graduate',
        'date_graduated',
        'so_number',
        'so_date',
        'suspend_account',
        'suspend_remarks',
    ];

    protected $casts = [
        'is_graduate' => 'boolean',
        'suspend_account' => 'boolean',
        'date_graduated' => 'date',
        'so_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
