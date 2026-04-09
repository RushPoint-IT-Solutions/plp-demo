<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StudentProfileOptionValue extends Model
{
    protected $table = 'student_profile_option_values';

    protected $fillable = [
        'student_profile_id',
        'domain',
        'option_lookup_id',
        'value_text',
        'sort_order',
    ];

    public function studentProfile()
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function option()
    {
        return $this->belongsTo(StudentProfileOptionLookup::class, 'option_lookup_id');
    }
}
