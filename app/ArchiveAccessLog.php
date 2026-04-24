<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ArchiveAccessLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'archive_access_logs';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'archive_id',
        'user_id',
        'action',
        'ip_address',
        'user_agent',
        'details',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'details' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Action constants
     */
    const ACTION_VIEW = 'view';
    const ACTION_DOWNLOAD = 'download';
    const ACTION_RESTORE = 'restore';
    const ACTION_RETRIEVE_REQUEST = 'retrieve_request';
    const ACTION_RETRIEVE_APPROVE = 'retrieve_approve';
    const ACTION_RETRIEVE_REJECT = 'retrieve_reject';
    const ACTION_DISPOSAL_REQUEST = 'disposal_request';
    const ACTION_DISPOSAL_APPROVE = 'disposal_approve';
    const ACTION_DISPOSAL_REJECT = 'disposal_reject';
    const ACTION_DISPOSAL_EXECUTE = 'disposal_execute';
    const ACTION_DELETE = 'delete';

    /**
     * Get the archive being accessed.
     */
    public function archive()
    {
        return $this->belongsTo(Archive::class);
    }

    /**
     * Get the user who performed the action.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create a log entry for an archive action.
     */
    public static function logAccess($archiveId, $userId, $action, $details = null)
    {
        return self::create([
            'archive_id' => $archiveId,
            'user_id' => $userId,
            'action' => $action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'details' => $details,
            'created_at' => now(),
        ]);
    }
}