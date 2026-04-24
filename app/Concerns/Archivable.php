<?php

namespace App\Concerns;

/**
 * Trait for models that can be archived
 * 
 * Provides common functionality for archivable models including
 * global scopes to filter archived records and metadata methods.
 */
trait Archivable
{
    /**
     * Boot the archivable trait
     */
    public static function bootArchivable(): void
    {
        // Add global scope to exclude archived records by default
        static::addGlobalScope('not_archived', function ($query) {
            $table = $query->getModel()->getTable();
            if (in_array('is_archived', $query->getModel()->getFillable())) {
                $query->where($table . '.is_archived', false);
            }
        });
    }

    /**
     * Get the archive metadata for this record
     */
    public function getArchiveMetadata(): array
    {
        return [
            'id' => $this->id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Get display name for archive listing
     */
    public function getArchiveDisplayName(): string
    {
        if (isset($this->name)) {
            return $this->name;
        }
        if (isset($this->title)) {
            return $this->title;
        }
        if (isset($this->student_id)) {
            return "Student #{$this->student_id}";
        }
        if (isset($this->faculty_id)) {
            return "Faculty #{$this->faculty_id}";
        }

        return static::class . " #{$this->id}";
    }

    /**
     * Scope to include archived records
     */
    public function scopeWithArchived($query)
    {
        return $query->withoutGlobalScope('not_archived');
    }

    /**
     * Scope to get only archived records
     */
    public function scopeArchived($query)
    {
        return $query->withoutGlobalScope('not_archived')
            ->where('is_archived', true);
    }

    /**
     * Check if this record is archived
     */
    public function isArchived(): bool
    {
        return $this->is_archived ?? false;
    }

    /**
     * Get the archive category for this record type
     */
    public function getArchiveCategory(): string
    {
        $className = class_basename(static::class);
        
        $academicTypes = ['StudentGradeRecord', 'StudentDeficiency', 'StudentDisciplineRecord', 'StudentSubjectGrade'];
        $personnelTypes = ['MasterFacultyFile', 'FacultyLoad'];
        $administrativeTypes = ['SlotMonitoring', 'RoomCourseAssignment', 'SectionOffering', 'SystemAnnouncement'];

        if (in_array($className, $academicTypes)) {
            return 'academic';
        }
        if (in_array($className, $personnelTypes)) {
            return 'personnel';
        }
        if (in_array($className, $administrativeTypes)) {
            return 'administrative';
        }

        return 'compliance';
    }
}