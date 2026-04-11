<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SectionMergingOperation extends Model
{
    protected $fillable = [
        'school_year',
        'semester',
        'source_slot_monitoring_id',
        'target_slot_monitoring_id',
        'source_course_id',
        'target_course_id',
        'source_section',
        'target_section',
        'source_subject',
        'target_subject',
        'source_total_slots',
        'source_enrolled_slots',
        'target_total_slots_before',
        'target_enrolled_slots_before',
        'target_total_slots_after',
        'target_enrolled_slots_after',
        'merge_status',
        'merged_by_user_id',
        'merged_at',
    ];

    protected $dates = [
        'merged_at',
        'created_at',
        'updated_at',
    ];

    public function sourceSlot()
    {
        return $this->belongsTo(SlotMonitoring::class, 'source_slot_monitoring_id');
    }

    public function targetSlot()
    {
        return $this->belongsTo(SlotMonitoring::class, 'target_slot_monitoring_id');
    }

    public function sourceCourse()
    {
        return $this->belongsTo(Course::class, 'source_course_id');
    }

    public function targetCourse()
    {
        return $this->belongsTo(Course::class, 'target_course_id');
    }

    public function mergedByUser()
    {
        return $this->belongsTo(User::class, 'merged_by_user_id');
    }
}
