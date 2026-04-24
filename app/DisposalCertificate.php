<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DisposalCertificate extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'disposal_certificates';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'archive_id',
        'disposed_by',
        'disposal_method',
        'verification_hash',
        'certificate_number',
        'disposal_details',
        'disposed_at',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'disposed_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Disposal method constants
     */
    const METHOD_SECURE_DELETE = 'secure_delete';
    const METHOD_ANONYMIZE = 'anonymize';
    const METHOD_EXPORT_THEN_DELETE = 'export_then_delete';

    /**
     * Get the archive that was disposed.
     */
    public function archive()
    {
        return $this->belongsTo(Archive::class);
    }

    /**
     * Get the user who performed the disposal.
     */
    public function disposer()
    {
        return $this->belongsTo(User::class, 'disposed_by');
    }

    /**
     * Generate a unique certificate number.
     */
    public static function generateCertificateNumber()
    {
        $year = date('Y');
        $sequence = self::whereYear('disposed_at', $year)->count() + 1;
        return sprintf('DISP-%s-%06d', $year, $sequence);
    }

    /**
     * Generate verification hash for secure disposal.
     */
    public static function generateVerificationHash($archiveId, $disposalMethod, $timestamp)
    {
        return hash('sha256', "{$archiveId}:{$disposalMethod}:{$timestamp}:" . config('app.key'));
    }
}