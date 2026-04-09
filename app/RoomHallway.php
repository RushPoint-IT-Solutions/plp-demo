<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RoomHallway extends Model
{
    protected $fillable = [
        'room_building_id',
        'name',
    ];

    public function building()
    {
        return $this->belongsTo(RoomBuilding::class, 'room_building_id');
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
}
