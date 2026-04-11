<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StudentProfileOptionLookup extends Model
{
    protected $table = 'student_profile_option_lookups';

    protected $fillable = [
        'domain',
        'code',
        'label',
        'is_other',
    ];

    protected $casts = [
        'is_other' => 'boolean',
    ];

    public function values()
    {
        return $this->hasMany(StudentProfileOptionValue::class, 'option_lookup_id');
    }
}
