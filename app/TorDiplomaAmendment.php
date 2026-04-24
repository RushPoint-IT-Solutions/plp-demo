<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TorDiplomaAmendment extends Model
{
    protected $fillable = [
        'tor_diploma_document_id',
        'amended_by_user_id',
        'amendment_type',
        'description',
        'old_value',
        'new_value',
    ];

    public function document()
    {
        return $this->belongsTo(TorDiplomaDocument::class, 'tor_diploma_document_id');
    }

    public function amendedBy()
    {
        return $this->belongsTo(User::class, 'amended_by_user_id');
    }
}
