<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserAccountState extends Model
{
    protected $fillable = [
        'code',
        'name',
    ];

    public function profiles()
    {
        return $this->hasMany(UserAccountProfile::class, 'user_account_state_id');
    }
}
