<?php

namespace App;

use App\Concerns\ResolvesLookupCodeFields;
use Illuminate\Database\Eloquent\Model;

class MasterFacultyFile extends Model
{
    use ResolvesLookupCodeFields;

    protected $table = 'master_faculty_files';

    protected $fillable = [
        'code',
        'source_faculty_id',
        'name',
        'department',
        'status',
        'status_id',
        'config_payload',
        'snapshot_taken_at',
        'snapshot_note',
        'is_snapshot',
    ];

    protected $casts = [
        'config_payload' => 'array',
        'snapshot_taken_at' => 'datetime',
        'is_snapshot' => 'boolean',
    ];

    public function sourceFaculty()
    {
        return $this->belongsTo(Faculty::class, 'source_faculty_id');
    }

    public function statusLookup()
    {
        return $this->belongsTo(MasterFacultyStatus::class, 'status_id');
    }

    public function getStatusAttribute($value)
    {
        return $this->getLookupCodeAttributeValue('status', 'statusLookup', $value);
    }

    public function setStatusAttribute($value)
    {
        $this->setLookupCodeAttributeValue('status', 'status_id', MasterFacultyStatus::class, $value);
    }
}
