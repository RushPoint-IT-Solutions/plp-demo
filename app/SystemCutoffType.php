<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SystemCutoffType extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    public function cutoffEntries()
    {
        return $this->hasMany(SystemCutoffEntry::class, 'cutoff_type_id');
    }
}
