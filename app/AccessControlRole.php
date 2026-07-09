<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccessControlRole extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function roleAccessControls()
    {
        return $this->hasMany(RoleAccessControl::class, 'role_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'access_control_role_id');
    }
}
