<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ParentContactRequest extends Model
{
    protected $fillable = [
        'reference_no',
        'parent_id',
        'student_id',
        'topic_id',
        'status_id',
        'channel_id',
        'submitted_by_user_id',
        'resolved_by_user_id',
        'subject',
        'message',
        'contact_email_snapshot',
        'contact_mobile_snapshot',
        'submitted_at',
        'resolved_at',
        'resolution_notes',
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'student_id' => 'integer',
        'topic_id' => 'integer',
        'status_id' => 'integer',
        'channel_id' => 'integer',
        'submitted_by_user_id' => 'integer',
        'resolved_by_user_id' => 'integer',
        'submitted_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function parentAccount()
    {
        return $this->belongsTo(ParentAccount::class, 'parent_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function topic()
    {
        return $this->belongsTo(ParentContactRequestTopic::class, 'topic_id');
    }

    public function status()
    {
        return $this->belongsTo(ParentContactRequestStatus::class, 'status_id');
    }

    public function channel()
    {
        return $this->belongsTo(ParentContactRequestChannel::class, 'channel_id');
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }
}
