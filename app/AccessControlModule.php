<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccessControlModule extends Model
{
    protected $fillable = [
        'code',
        'name',
        'parent_id',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function userAccessControls()
    {
        return $this->hasMany(UserAccessControl::class, 'access_control_module_id');
    }
}
