<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApplicantOnboardingAcknowledgement extends Model
{
    protected $fillable = [
        'applicant_id',
        'ack_notices',
        'ack_terms',
        'acknowledged_at',
    ];

    protected $casts = [
        'ack_notices' => 'boolean',
        'ack_terms' => 'boolean',
        'acknowledged_at' => 'datetime',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
}
