<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccessControlPermissionType extends Model
{
    protected $fillable = [
        'code',
        'label',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function userAccessControls()
    {
        return $this->hasMany(UserAccessControl::class, 'access_control_permission_type_id');
    }
}
