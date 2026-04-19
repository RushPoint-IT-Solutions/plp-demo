<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserAccountProfile extends Model
{
    protected $fillable = [
        'user_id',
        'user_account_type_id',
        'user_account_state_id',
        'is_sample',
    ];

    protected $casts = [
        'is_sample' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function type()
    {
        return $this->belongsTo(UserAccountType::class, 'user_account_type_id');
    }

    public function state()
    {
        return $this->belongsTo(UserAccountState::class, 'user_account_state_id');
    }
}
