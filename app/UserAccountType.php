<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserAccountType extends Model
{
    protected $fillable = [
        'code',
        'name',
    ];

    public function profiles()
    {
        return $this->hasMany(UserAccountProfile::class, 'user_account_type_id');
    }
}
