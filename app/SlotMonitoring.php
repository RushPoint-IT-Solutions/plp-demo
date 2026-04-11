<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SlotMonitoring extends Model
{
    protected $table = 'slot_monitorings';

    protected $fillable = [
        'school_year',
        'semester',
        'course_id',
        'section',
        'subject',
        'schedule',
        'total_slots',
        'enrolled_slots',
        'updated_by_user_id',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }
}
