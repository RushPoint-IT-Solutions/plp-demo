<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RetentionPolicy extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'retention_policies';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'record_type',
        'retention_period_months',
        'archive_trigger',
        'inactivity_months',
        'disposal_trigger',
        'disposal_after_graduation_years',
        'requires_approval',
        'approval_role',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'retention_period_months' => 'integer',
        'inactivity_months' => 'integer',
        'disposal_after_graduation_years' => 'integer',
        'requires_approval' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the archives using this policy.
     */
    public function archives()
    {
        return $this->hasMany(Archive::class);
    }

    /**
     * Get the archive categories using this policy.
     */
    public function archiveCategories()
    {
        return $this->hasMany(ArchiveCategory::class);
    }

    /**
     * Calculate the scheduled disposal date based on policy.
     */
    public function calculateDisposalDate($archivedAt)
    {
        $disposalMonths = $this->disposal_after_graduation_years 
            ? $this->disposal_after_graduation_years * 12 
            : $this->retention_period_months;
        
        return $archivedAt->copy()->addMonths($disposalMonths);
    }

    /**
     * Scope a query to only include active policies.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include policies for a given record type.
     */
    public function scopeForRecordType($query, $recordType)
    {
        return $query->where('record_type', $recordType);
    }
}