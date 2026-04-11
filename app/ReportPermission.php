<?php

namespace App;

use App\Concerns\ResolvesLookupCodeFields;
use Illuminate\Database\Eloquent\Model;

class ReportPermission extends Model
{
    use ResolvesLookupCodeFields;

    protected $fillable = [
        'user_id',
        'report_key',
        'report_type',
        'report_type_id',
        'is_allowed',
    ];

    protected $casts = [
        'is_allowed' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reportTypeLookup()
    {
        return $this->belongsTo(ReportType::class, 'report_type_id');
    }

    public function getReportTypeAttribute($value)
    {
        return $this->getLookupCodeAttributeValue('report_type', 'reportTypeLookup', $value);
    }

    public function setReportTypeAttribute($value)
    {
        $this->setLookupCodeAttributeValue('report_type', 'report_type_id', ReportType::class, $value);
    }
}
