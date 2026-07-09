<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RoleAccessControl extends Model
{
    protected $fillable = [
        'role_id',
        'access_control_module_id',
        'access_control_permission_type_id',
        'is_allowed',
    ];

    protected $casts = [
        'is_allowed' => 'boolean',
    ];

    public function role()
    {
        return $this->belongsTo(AccessControlRole::class, 'role_id');
    }

    public function module()
    {
        return $this->belongsTo(AccessControlModule::class, 'access_control_module_id');
    }

    public function permissionType()
    {
        return $this->belongsTo(AccessControlPermissionType::class, 'access_control_permission_type_id');
    }
}
