<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ParentStudentLink extends Model
{
    protected $fillable = [
        'parent_id',
        'student_id',
        'relationship_type_id',
        'is_primary_contact',
        'receives_notifications',
    ];

    protected $casts = [
        'is_primary_contact' => 'boolean',
        'receives_notifications' => 'boolean',
    ];

    public function parentAccount()
    {
        return $this->belongsTo(ParentAccount::class, 'parent_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function relationshipType()
    {
        return $this->belongsTo(ParentRelationshipType::class, 'relationship_type_id');
    }
}
