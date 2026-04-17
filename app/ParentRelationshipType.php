<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ParentRelationshipType extends Model
{
    protected $fillable = [
        'code',
        'name',
    ];

    public function parentStudentLinks()
    {
        return $this->hasMany(ParentStudentLink::class, 'relationship_type_id');
    }
}
