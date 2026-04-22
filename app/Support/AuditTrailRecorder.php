<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AuditTrailRecorder
{
    public static function record(string $eventCode, array $subjects = [], array $metadata = []): bool
    {
        if (!self::tablesReady() || !count($subjects)) {
            return false;
        }

        try {
            $now = Carbon::now();
            $actorUserId = array_key_exists('actor_user_id', $metadata)
                ? ($metadata['actor_user_id'] !== null ? (int) $metadata['actor_user_id'] : null)
                : optional(auth()->user())->id;

            $eventId = DB::table('audit_events')->insertGetId([
                'event_code' => strtoupper(trim($eventCode)),
                'actor_user_id' => $actorUserId,
                'source_module' => self::stringify($metadata['source_module'] ?? 'Registrar'),
                'source_action' => self::stringify($metadata['source_action'] ?? ''),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($subjects as $subject) {
                if (!is_array($subject)) {
                    continue;
                }

                $subjectId = DB::table('audit_event_subjects')->insertGetId([
                    'audit_event_id' => $eventId,
                    'subject_type' => self::stringify($subject['type'] ?? $subject['subject_type'] ?? 'Unknown'),
                    'subject_id' => array_key_exists('id', $subject) && $subject['id'] !== null
                        ? (int) $subject['id']
                        : (array_key_exists('subject_id', $subject) && $subject['subject_id'] !== null ? (int) $subject['subject_id'] : null),
                    'subject_label' => self::stringify($subject['label'] ?? $subject['subject_label'] ?? ''),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                foreach ((array) ($subject['changes'] ?? []) as $change) {
                    if (!is_array($change)) {
                        continue;
                    }

                    $fieldName = trim((string) ($change['field'] ?? $change['field_name'] ?? ''));
                    if ($fieldName === '') {
                        continue;
                    }

                    DB::table('audit_event_changes')->insert([
                        'audit_event_subject_id' => $subjectId,
                        'field_name' => $fieldName,
                        'old_value' => self::stringify($change['old'] ?? $change['old_value'] ?? null),
                        'new_value' => self::stringify($change['new'] ?? $change['new_value'] ?? null),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            return true;
        } catch (Throwable $exception) {
            logger()->warning('Audit trail recording failed.', [
                'event_code' => strtoupper(trim($eventCode)),
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    private static function tablesReady(): bool
    {
        return Schema::hasTable('audit_events')
            && Schema::hasTable('audit_event_subjects')
            && Schema::hasTable('audit_event_changes');
    }

    private static function stringify($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_scalar($value)) {
            return trim((string) $value);
        }

        $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $encoded !== false ? $encoded : null;
    }
}