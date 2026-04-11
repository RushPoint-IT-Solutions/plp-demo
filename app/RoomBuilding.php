<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RoomBuilding extends Model
{
    protected $fillable = [
        'name',
    ];

    public function hallways()
    {
        return $this->hasMany(RoomHallway::class);
    }
}
