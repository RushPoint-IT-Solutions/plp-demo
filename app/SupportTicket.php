<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    protected $fillable = [
        'ticket_no',
        'requester_type',
        'requester_name',
        'requester_email',
        'category',
        'subject',
        'message',
        'priority',
        'status',
        'assigned_to_user_id',
        'created_by_user_id',
        'resolved_at',
    ];

    protected $dates = [
        'resolved_at',
        'created_at',
        'updated_at',
    ];

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
