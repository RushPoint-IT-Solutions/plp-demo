<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ArchiveRetrievalRequest extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'archive_retrieval_requests';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'archive_id',
        'requested_by',
        'request_reason',
        'access_level',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'fulfilled_at',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'reviewed_at' => 'datetime',
        'fulfilled_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_FULFILLED = 'fulfilled';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Access level constants
     */
    const ACCESS_VIEW = 'view';
    const ACCESS_DOWNLOAD = 'download';
    const ACCESS_RESTORE_TEMPORARY = 'restore_temporary';

    /**
     * Get the archive being requested.
     */
    public function archive()
    {
        return $this->belongsTo(Archive::class);
    }

    /**
     * Get the user who requested.
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get the user who reviewed the request.
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Check if the request is pending.
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the request is approved.
     */
    public function isApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if the request has expired.
     */
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Scope a query to only include pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include requests for a given user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('requested_by', $userId);
    }
}