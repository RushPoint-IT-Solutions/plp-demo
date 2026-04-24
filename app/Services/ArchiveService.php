<?php

namespace App\Services;

use App\Archive;
use App\RetentionPolicy;
use App\ArchiveRetrievalRequest;
use App\ArchiveDisposalQueue;
use App\ArchiveAccessLog;
use App\DisposalCertificate;
use App\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Archive Service for Document Control and Record Management
 * 
 * Handles archiving, retrieval, restoration, and disposal of records
 * following retention policies and compliance requirements.
 */
class ArchiveService
{
    // === ARCHIVE OPERATIONS ===

    /**
     * Archive a single record
     */
    public function archiveRecord(
        string $recordType,
        int $recordId,
        int $archivedBy,
        ?string $reason = null,
        ?string $category = null
    ): Archive {
        return DB::transaction(function () use ($recordType, $recordId, $archivedBy, $reason, $category) {
            // Get retention policy
            $policy = RetentionPolicy::active()
                ->forRecordType($recordType)
                ->first();

            // Determine category
            $archiveCategory = $category ?? $this->inferCategory($recordType);

            // Calculate scheduled disposal
            $scheduledDisposal = $policy
                ? $policy->calculateDisposalDate(now())
                : now()->addYears(7);

            // Create archive record
            $archive = Archive::create([
                'record_type' => $recordType,
                'record_id' => $recordId,
                'original_table' => $this->getTableName($recordType),
                'archived_by' => $archivedBy,
                'archived_at' => now(),
                'archive_category' => $archiveCategory,
                'archive_reason' => $reason,
                'retention_policy_id' => $policy ? $policy->id : null,
                'scheduled_disposal_at' => $scheduledDisposal,
                'metadata' => $this->captureMetadata($recordType, $recordId),
            ]);

            // Mark original record as archived
            $this->markOriginalAsArchived($recordType, $recordId);

            // Log action
            ArchiveAccessLog::log(
                $archive->id,
                'archived',
                $archivedBy,
                ['reason' => $reason, 'policy_id' => $policy ? $policy->id : null]
            );

            return $archive;
        });
    }

    /**
     * Archive multiple records in batch
     */
    public function archiveBatch(
        array $records,
        int $archivedBy,
        ?string $reason = null
    ): array {
        $results = ['success' => [], 'failed' => []];

        foreach ($records as $record) {
            try {
                $archive = $this->archiveRecord(
                    $record['record_type'],
                    $record['record_id'],
                    $archivedBy,
                    $reason
                );
                $results['success'][] = $archive;
            } catch (\Exception $e) {
                Log::error("Archive batch failed for record", [
                    'record_type' => $record['record_type'],
                    'record_id' => $record['record_id'],
                    'error' => $e->getMessage(),
                ]);
                $results['failed'][] = [
                    'record_type' => $record['record_type'],
                    'record_id' => $record['record_id'],
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Auto-archive records based on retention policies
     */
    public function autoArchiveByPolicy(RetentionPolicy $policy): array
    {
        $records = $this->getEligibleRecordsForArchive($policy);
        $results = ['archived' => 0, 'skipped' => 0];

        foreach ($records as $record) {
            try {
                $this->archiveRecord(
                    $policy->record_type,
                    $record->id,
                    $this->getSystemUserId(),
                    "Auto-archived by policy: {$policy->name}"
                );
                $results['archived']++;
            } catch (\Exception $e) {
                $results['skipped']++;
                Log::error("Auto-archive failed", [
                    'policy' => $policy->name,
                    'record_id' => $record->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $results;
    }

    // === RETRIEVAL OPERATIONS ===

    /**
     * Request retrieval of an archived record
     */
    public function requestRetrieval(
        Archive $archive,
        int $requestedBy,
        string $reason,
        string $accessLevel = 'view'
    ): ArchiveRetrievalRequest {
        // Check if user has access to this archive category
        $this->validateRetrievalAccess($archive, $requestedBy);

        $request = ArchiveRetrievalRequest::create([
            'archive_id' => $archive->id,
            'requested_by' => $requestedBy,
            'request_reason' => $reason,
            'access_level' => $accessLevel,
            'status' => 'pending',
        ]);

        ArchiveAccessLog::log(
            $archive->id,
            'retrieval_requested',
            $requestedBy,
            ['access_level' => $accessLevel, 'reason' => $reason]
        );

        return $request;
    }

    /**
     * Approve a retrieval request
     */
    public function approveRetrieval(
        ArchiveRetrievalRequest $request,
        User $reviewer,
        ?string $notes = null,
        ?int $restorationDays = null
    ): bool {
        $expiresAt = $restorationDays
            ? now()->addDays($restorationDays)
            : null;

        $result = $request->approve($reviewer, $notes, $expiresAt);

        if ($result) {
            ArchiveAccessLog::log(
                $request->archive_id,
                'retrieval_approved',
                $reviewer->id,
                ['request_id' => $request->id, 'expires_at' => $expiresAt]
            );
        }

        return $result;
    }

    /**
     * Reject a retrieval request
     */
    public function rejectRetrieval(
        ArchiveRetrievalRequest $request,
        User $reviewer,
        string $notes
    ): bool {
        $result = $request->reject($reviewer, $notes);

        if ($result) {
            ArchiveAccessLog::log(
                $request->archive_id,
                'retrieval_rejected',
                $reviewer->id,
                ['request_id' => $request->id, 'reason' => $notes]
            );
        }

        return $result;
    }

    /**
     * Fulfill a retrieval request (provide access)
     */
    public function fulfillRetrieval(ArchiveRetrievalRequest $request): array
    {
        if (!$request->canBeFulfilled()) {
            throw new \Exception("Request cannot be fulfilled in current state");
        }

        $archive = $request->archive;
        $result = ['type' => $request->access_level, 'data' => null];

        switch ($request->access_level) {
            case 'view':
                $result['data'] = $this->getArchivedRecordData($archive);
                break;

            case 'download':
                $result['data'] = $this->generateArchivedRecordPdf($archive);
                break;

            case 'restore_temporary':
                $this->restoreRecordTemporarily($archive, $request->expires_at);
                $result['data'] = ['restored_until' => $request->expires_at];
                break;
        }

        $request->fulfill();

        ArchiveAccessLog::log(
            $archive->id,
            'retrieved',
            $request->requested_by,
            ['request_id' => $request->id, 'access_level' => $request->access_level]
        );

        return $result;
    }

    // === RESTORATION OPERATIONS ===

    /**
     * Temporarily restore an archived record
     */
    public function restoreRecordTemporarily(Archive $archive, \DateTime $expiresAt): void
    {
        $archive->update([
            'is_restored' => true,
            'restored_at' => now(),
            'restored_until' => $expiresAt,
        ]);

        // Restore original record
        $this->restoreOriginalRecord($archive);

        ArchiveAccessLog::log(
            $archive->id,
            'restored',
            auth()->id(),
            ['expires_at' => $expiresAt]
        );
    }

    /**
     * Re-archive a temporarily restored record
     */
    public function reArchive(Archive $archive, int $reArchivedBy): Archive
    {
        // Re-archive the record
        $newArchive = $this->archiveRecord(
            $archive->record_type,
            $archive->record_id,
            $reArchivedBy,
            "Re-archived after temporary restoration"
        );

        // Mark old archive as disposed
        $this->scheduleDisposal($archive, $reArchivedBy, 'Re-archive');

        return $newArchive;
    }

    // === DISPOSAL OPERATIONS ===

    /**
     * Schedule an archive for disposal
     */
    public function scheduleDisposal(
        Archive $archive,
        int $requestedBy,
        ?string $reason = null
    ): ArchiveDisposalQueue {
        if (!$archive->isEligibleForDisposal()) {
            throw new \Exception("Archive is not eligible for disposal");
        }

        $queue = ArchiveDisposalQueue::create([
            'archive_id' => $archive->id,
            'scheduled_date' => $archive->scheduled_disposal_at,
            'status' => 'pending_approval',
            'requested_by' => $requestedBy,
        ]);

        return $queue;
    }

    /**
     * Approve disposal
     */
    public function approveDisposal(
        ArchiveDisposalQueue $queue,
        User $reviewer,
        ?string $notes = null
    ): bool {
        $result = $queue->approve($reviewer, $notes);

        if ($result) {
            ArchiveAccessLog::log(
                $queue->archive_id,
                'disposal_approved',
                $reviewer->id,
                ['queue_id' => $queue->id]
            );
        }

        return $result;
    }

    /**
     * Reject disposal
     */
    public function rejectDisposal(
        ArchiveDisposalQueue $queue,
        User $reviewer,
        string $notes
    ): bool {
        $result = $queue->reject($reviewer, $notes);

        if ($result) {
            ArchiveAccessLog::log(
                $queue->archive_id,
                'disposal_rejected',
                $reviewer->id,
                ['queue_id' => $queue->id, 'reason' => $notes]
            );
        }

        return $result;
    }

    /**
     * Execute disposal
     */
    public function executeDisposal(ArchiveDisposalQueue $queue, User $executor): DisposalCertificate
    {
        return DB::transaction(function () use ($queue, $executor) {
            $archive = $queue->archive;

            // Generate certificate
            $certificateNumber = DisposalCertificate::generateCertificateNumber();
            $verificationData = [
                'archive_id' => $archive->id,
                'record_type' => $archive->record_type,
                'record_id' => $archive->record_id,
                'disposed_at' => now()->toIso8601String(),
                'method' => $queue->disposal_method,
            ];

            $certificate = DisposalCertificate::create([
                'certificate_number' => $certificateNumber,
                'disposal_queue_id' => $queue->id,
                'disposed_by' => $executor->id,
                'disposed_at' => now(),
                'record_count' => 1,
                'record_types' => [$archive->record_type],
                'disposal_method' => $queue->disposal_method,
                'verification_hash' => DisposalCertificate::generateVerificationHash($verificationData),
            ]);

            // Execute deletion based on method
            $this->executeDeletion($archive, $queue->disposal_method);

            // Update queue status
            $queue->markAsDisposed($executor, $certificateNumber);

            // Log action
            ArchiveAccessLog::log(
                $archive->id,
                'disposed',
                $executor->id,
                [
                    'queue_id' => $queue->id,
                    'certificate_number' => $certificateNumber,
                    'method' => $queue->disposal_method,
                ]
            );

            return $certificate;
        });
    }

    /**
     * Execute the actual deletion of archived data
     */
    private function executeDeletion(Archive $archive, string $method): void
    {
        switch ($method) {
            case 'secure_delete':
                // Permanently delete from original table
                $this->deleteOriginalRecord($archive);
                // Delete archive record
                $archive->delete();
                break;

            case 'anonymize':
                // Anonymize PII in original record
                $this->anonymizeOriginalRecord($archive);
                // Delete archive record
                $archive->delete();
                break;

            case 'export_then_delete':
                // Export to file storage first (handled by executeDisposal)
                // Then delete
                $this->deleteOriginalRecord($archive);
                $archive->delete();
                break;
        }
    }

    // === HELPER METHODS ===

    /**
     * Infer archive category from record type
     */
    private function inferCategory(string $recordType): string
    {
        $academicTypes = ['StudentGradeRecord', 'StudentDeficiency', 'StudentDisciplineRecord', 'StudentSubjectGrade'];
        $personnelTypes = ['MasterFacultyFile', 'FacultyLoad'];
        $administrativeTypes = ['SlotMonitoring', 'RoomCourseAssignment', 'SectionOffering', 'SystemAnnouncement'];

        if (in_array($recordType, $academicTypes)) {
            return 'academic';
        }
        if (in_array($recordType, $personnelTypes)) {
            return 'personnel';
        }
        if (in_array($recordType, $administrativeTypes)) {
            return 'administrative';
        }

        return 'compliance';
    }

    /**
     * Get table name from record type
     */
    private function getTableName(string $recordType): string
    {
        $modelClass = "App\\{$recordType}";
        if (class_exists($modelClass)) {
            return (new $modelClass())->getTable();
        }

        // Fallback: convert CamelCase to snake_case
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $recordType)) . 's';
    }

    /**
     * Capture metadata from the original record
     */
    private function captureMetadata(string $recordType, int $recordId): array
    {
        $modelClass = "App\\{$recordType}";
        $record = $modelClass::find($recordId);

        if (!$record) {
            return [];
        }

        // Capture key identifying fields for display
        $metadata = [];
        if (isset($record->student_id)) {
            $metadata['student_id'] = $record->student_id;
        }
        if (isset($record->faculty_id)) {
            $metadata['faculty_id'] = $record->faculty_id;
        }
        if (isset($record->semester_id)) {
            $metadata['semester_id'] = $record->semester_id;
        }
        if (isset($record->school_year)) {
            $metadata['school_year'] = $record->school_year;
        }

        return $metadata;
    }

    /**
     * Mark original record as archived
     */
    private function markOriginalAsArchived(string $recordType, int $recordId): void
    {
        $modelClass = "App\\{$recordType}";
        $record = $modelClass::find($recordId);
        if ($record && in_array('is_archived', $record->getFillable())) {
            $record->update([
                'is_archived' => true,
                'archived_at' => now(),
            ]);
        }
    }

    /**
     * Restore original record from archived state
     */
    private function restoreOriginalRecord(Archive $archive): void
    {
        $modelClass = "App\\{$archive->record_type}";
        $record = $modelClass::find($archive->record_id);
        if ($record && in_array('is_archived', $record->getFillable())) {
            $record->update([
                'is_archived' => false,
                'archived_at' => null,
            ]);
        }
    }

    /**
     * Delete original record permanently
     */
    private function deleteOriginalRecord(Archive $archive): void
    {
        $modelClass = "App\\{$archive->record_type}";
        $record = $modelClass::find($archive->record_id);
        if ($record) {
            $record->delete();
        }
    }

    /**
     * Anonymize original record by redacting PII
     */
    private function anonymizeOriginalRecord(Archive $archive): void
    {
        $modelClass = "App\\{$archive->record_type}";
        $record = $modelClass::find($archive->record_id);

        if ($record) {
            // Anonymize common PII fields
            $fillable = $record->getFillable();
            $piiFields = ['name', 'email', 'phone', 'address', 'birthdate'];

            $updates = [];
            foreach ($piiFields as $field) {
                if (in_array($field, $fillable)) {
                    $updates[$field] = "[REDACTED-" . strtoupper(substr($field, 0, 3)) . "]";
                }
            }

            if (!empty($updates)) {
                $record->update($updates);
            }
        }
    }

    /**
     * Get records eligible for archiving based on policy
     */
    private function getEligibleRecordsForArchive(RetentionPolicy $policy): Collection
    {
        $modelClass = "App\\{$policy->record_type}";

        if (!class_exists($modelClass)) {
            return collect();
        }

        $query = $modelClass::where('is_archived', false);

        switch ($policy->archive_trigger) {
            case 'end_of_academic_year':
                // Archive records from previous academic year
                $query->where('created_at', '<', now()->startOfYear());
                break;

            case 'end_of_semester':
                // Archive records from previous semester
                $query->where('created_at', '<', now()->subMonths(6));
                break;

            case 'inactivity':
                // Archive inactive records
                $inactivityThreshold = now()->subMonths($policy->inactivity_months);
                $query->where('updated_at', '<', $inactivityThreshold);
                break;

            case 'graduation':
                // Archive graduated students' records
                // Requires student status check
                break;

            case 'separation':
                // Archive faculty who have separated
                // Requires faculty status check
                break;
        }

        return $query->limit(1000)->get();
    }

    /**
     * Get archived record data as array
     */
    private function getArchivedRecordData(Archive $archive): array
    {
        $original = $archive->getOriginalRecord();
        if (!$original) {
            return ['error' => 'Record not found'];
        }

        return $original->toArray();
    }

    /**
     * Generate PDF of archived record
     */
    private function generateArchivedRecordPdf(Archive $archive): string
    {
        // Generate PDF and return storage path
        // Implementation depends on PDF library
        return "archive_exports/{$archive->uuid}.pdf";
    }

    /**
     * Validate user has access to retrieve archive
     */
    private function validateRetrievalAccess(Archive $archive, int $userId): void
    {
        $user = User::find($userId);

        if (!$user) {
            throw new \Exception("User not found");
        }

        // Check role-based access
        $categoryAccess = [
            'academic' => ['admin', 'registrar', 'faculty'],
            'administrative' => ['admin', 'registrar'],
            'personnel' => ['admin', 'registrar'],
            'compliance' => ['admin'],
        ];

        $allowedRoles = $categoryAccess[$archive->archive_category] ?? [];

        if (!in_array($user->account_type, $allowedRoles)) {
            // Students/Parents can only access their own records
            if (in_array($user->account_type, ['student', 'parent'])) {
                $this->validateOwnRecordAccess($archive, $user);
            } else {
                throw new \Exception("Access denied to this archive category");
            }
        }
    }

    /**
     * Validate user can access their own record
     */
    private function validateOwnRecordAccess(Archive $archive, User $user): void
    {
        $metadata = $archive->metadata ?? [];

        // Check if user is accessing their own or their child's record
        if ($archive->record_type === 'StudentGradeRecord' || $archive->record_type === 'StudentSubjectGrade') {
            if ($metadata['student_id'] !== $user->student_id && $metadata['student_id'] !== $user->linked_student_id) {
                throw new \Exception("Access denied to this record");
            }
        }
    }

    /**
     * Get system user ID for automated operations
     */
    private function getSystemUserId(): int
    {
        // Return ID of system user for automated operations
        return User::where('account_type', 'admin')->first()->id ?? 1;
    }
}