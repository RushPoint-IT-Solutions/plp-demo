<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Archive extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'archives';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'record_type',
        'record_id',
        'original_table',
        'archived_by',
        'archived_at',
        'archive_category',
        'archive_reason',
        'retention_policy_id',
        'scheduled_disposal_at',
        'metadata',
        'is_restored',
        'restored_at',
        'restored_until',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'archived_at' => 'datetime',
        'scheduled_disposal_at' => 'datetime',
        'restored_at' => 'datetime',
        'restored_until' => 'datetime',
        'metadata' => 'array',
        'is_restored' => 'boolean',
    ];

    /**
     * Get the user who archived this record.
     */
    public function archivedBy()
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    /**
     * Get the retention policy associated with this archive.
     */
    public function retentionPolicy()
    {
        return $this->belongsTo(RetentionPolicy::class);
    }

    /**
     * Get the retrieval requests for this archive.
     */
    public function retrievalRequests()
    {
        return $this->hasMany(ArchiveRetrievalRequest::class);
    }

    /**
     * Get the disposal queue entry for this archive.
     */
    public function disposalQueue()
    {
        return $this->hasOne(ArchiveDisposalQueue::class);
    }

    /**
     * Get the disposal certificate for this archive.
     */
    public function disposalCertificate()
    {
        return $this->hasOne(DisposalCertificate::class);
    }

    /**
     * Get the access logs for this archive.
     */
    public function accessLogs()
    {
        return $this->hasMany(ArchiveAccessLog::class);
    }

    /**
     * Get the original record that was archived.
     */
    public function getOriginalRecordAttribute()
    {
        $modelClass = "App\\{$this->record_type}";
        if (class_exists($modelClass)) {
            return $modelClass::find($this->record_id);
        }
        return null;
    }

    /**
     * Scope a query to only include archives of a given category.
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('archive_category', $category);
    }

    /**
     * Scope a query to only include archives pending disposal.
     */
    public function scopePendingDisposal($query)
    {
        return $query->where('scheduled_disposal_at', '<=', now())
                    ->whereDoesntHave('disposalQueue', function ($q) {
                        $q->whereNotIn('status', ['disposed', 'cancelled']);
                    });
    }

    /**
     * Scope a query to only include restored archives.
     */
    public function scopeRestored($query)
    {
        return $query->where('is_restored', true);
    }

    /**
     * Check if this archive is currently restored.
     */
    public function isCurrentlyRestored()
    {
        return $this->is_restored && 
               $this->restored_until && 
               $this->restored_until->isFuture();
    }
}