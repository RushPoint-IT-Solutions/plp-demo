<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ArchiveDisposalQueue extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'archive_disposal_queue';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'archive_id',
        'scheduled_date',
        'status',
        'requested_by',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'disposed_by',
        'disposed_at',
        'disposal_method',
        'disposal_certificate_number',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'scheduled_date' => 'datetime',
        'reviewed_at' => 'datetime',
        'disposed_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_DISPOSED = 'disposed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Disposal method constants
     */
    const METHOD_SECURE_DELETE = 'secure_delete';
    const METHOD_ANONYMIZE = 'anonymize';
    const METHOD_EXPORT_THEN_DELETE = 'export_then_delete';

    /**
     * Get the archive being disposed.
     */
    public function archive()
    {
        return $this->belongsTo(Archive::class);
    }

    /**
     * Get the user who requested disposal.
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
     * Get the user who performed the disposal.
     */
    public function disposer()
    {
        return $this->belongsTo(User::class, 'disposed_by');
    }

    /**
     * Check if disposal is pending.
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING_APPROVAL;
    }

    /**
     * Check if disposal is ready to be executed.
     */
    public function isReadyForDisposal()
    {
        return $this->status === self::STATUS_APPROVED && 
               $this->scheduled_date->isPast();
    }

    /**
     * Scope a query to only include pending approval requests.
     */
    public function scopePendingApproval($query)
    {
        return $query->where('status', self::STATUS_PENDING_APPROVAL);
    }

    /**
     * Scope a query to only include ready for disposal.
     */
    public function scopeReadyForDisposal($query)
    {
        return $query->where('status', self::STATUS_APPROVED)
                    ->where('scheduled_date', '<=', now());
    }
}