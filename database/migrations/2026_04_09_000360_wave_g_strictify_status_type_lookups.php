<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WaveGStrictifyStatusTypeLookups extends Migration
{
    private function definitions()
    {
        return [
            [
                'table' => 'applicants',
                'legacy_column' => 'application_status',
                'id_column' => 'application_status_id',
                'lookup_table' => 'applicant_application_statuses',
                'length' => 120,
            ],
            [
                'table' => 'applicants',
                'legacy_column' => 'exam_result_status',
                'id_column' => 'exam_result_status_id',
                'lookup_table' => 'applicant_exam_result_statuses',
                'length' => 120,
            ],
            [
                'table' => 'academic_calendar_events',
                'legacy_column' => 'event_type',
                'id_column' => 'event_type_id',
                'lookup_table' => 'academic_event_types',
                'length' => 120,
                'legacy_index' => 'academic_calendar_events_event_type_index',
            ],
            [
                'table' => 'cancellation_waivers',
                'legacy_column' => 'status',
                'id_column' => 'status_id',
                'lookup_table' => 'waiver_statuses',
                'length' => 120,
            ],
            [
                'table' => 'cross_enrollment_requests',
                'legacy_column' => 'status',
                'id_column' => 'status_id',
                'lookup_table' => 'cross_enrollment_statuses',
                'length' => 120,
            ],
            [
                'table' => 'subjects',
                'legacy_column' => 'load_type',
                'id_column' => 'load_type_id',
                'lookup_table' => 'subject_load_types',
                'length' => 120,
            ],
            [
                'table' => 'subjects',
                'legacy_column' => 'grading_status',
                'id_column' => 'grading_status_id',
                'lookup_table' => 'subject_grading_statuses',
                'length' => 120,
            ],
            [
                'table' => 'system_announcements',
                'legacy_column' => 'announcement_type',
                'id_column' => 'announcement_type_id',
                'lookup_table' => 'announcement_types',
                'length' => 120,
            ],
            [
                'table' => 'certificates_issued',
                'legacy_column' => 'certificate_type',
                'id_column' => 'certificate_type_id',
                'lookup_table' => 'certificate_types',
                'length' => 120,
            ],
            [
                'table' => 'report_permissions',
                'legacy_column' => 'report_type',
                'id_column' => 'report_type_id',
                'lookup_table' => 'report_types',
                'length' => 120,
            ],
        ];
    }

    public function up()
    {
        $this->backfillLookupIds();
        $this->dropLegacyLookupColumns();
    }

    public function down()
    {
        $this->addLegacyLookupColumns();
        $this->restoreLegacyLookupValues();
        $this->restoreLegacyIndexes();
    }

    private function backfillLookupIds()
    {
        foreach ($this->definitions() as $definition) {
            $table = $definition['table'];
            $legacyColumn = $definition['legacy_column'];
            $idColumn = $definition['id_column'];
            $lookupTable = $definition['lookup_table'];

            if (!Schema::hasTable($table)
                || !Schema::hasTable($lookupTable)
                || !Schema::hasColumn($table, $legacyColumn)
                || !Schema::hasColumn($table, $idColumn)) {
                continue;
            }

            DB::statement(
                "UPDATE `{$table}` t
                LEFT JOIN `{$lookupTable}` l
                    ON LOWER(TRIM(l.code)) = LOWER(TRIM(t.`{$legacyColumn}`))
                SET t.`{$idColumn}` = l.id
                WHERE t.`{$idColumn}` IS NULL
                    AND t.`{$legacyColumn}` IS NOT NULL
                    AND TRIM(t.`{$legacyColumn}`) <> ''
                    AND l.id IS NOT NULL"
            );
        }
    }

    private function dropLegacyLookupColumns()
    {
        foreach ($this->definitions() as $definition) {
            $table = $definition['table'];
            $legacyColumn = $definition['legacy_column'];

            if (!Schema::hasTable($table) || !Schema::hasColumn($table, $legacyColumn)) {
                continue;
            }

            if (!empty($definition['legacy_index'])) {
                $this->dropIndexIfExists($table, $definition['legacy_index']);
            }

            Schema::table($table, function (Blueprint $tableBlueprint) use ($legacyColumn) {
                $tableBlueprint->dropColumn($legacyColumn);
            });
        }
    }

    private function addLegacyLookupColumns()
    {
        foreach ($this->definitions() as $definition) {
            $table = $definition['table'];
            $legacyColumn = $definition['legacy_column'];
            $idColumn = $definition['id_column'];
            $length = $definition['length'];

            if (!Schema::hasTable($table) || Schema::hasColumn($table, $legacyColumn)) {
                continue;
            }

            Schema::table($table, function (Blueprint $tableBlueprint) use ($table, $legacyColumn, $idColumn, $length) {
                if (Schema::hasColumn($table, $idColumn)) {
                    $tableBlueprint->string($legacyColumn, $length)->nullable()->after($idColumn);
                } else {
                    $tableBlueprint->string($legacyColumn, $length)->nullable();
                }
            });
        }
    }

    private function restoreLegacyLookupValues()
    {
        foreach ($this->definitions() as $definition) {
            $table = $definition['table'];
            $legacyColumn = $definition['legacy_column'];
            $idColumn = $definition['id_column'];
            $lookupTable = $definition['lookup_table'];

            if (!Schema::hasTable($table)
                || !Schema::hasTable($lookupTable)
                || !Schema::hasColumn($table, $legacyColumn)
                || !Schema::hasColumn($table, $idColumn)) {
                continue;
            }

            DB::statement(
                "UPDATE `{$table}` t
                JOIN `{$lookupTable}` l ON l.id = t.`{$idColumn}`
                SET t.`{$legacyColumn}` = COALESCE(NULLIF(TRIM(t.`{$legacyColumn}`), ''), l.code)
                WHERE t.`{$idColumn}` IS NOT NULL"
            );
        }
    }

    private function restoreLegacyIndexes()
    {
        if (Schema::hasTable('academic_calendar_events')
            && Schema::hasColumn('academic_calendar_events', 'event_type')
            && !$this->indexExists('academic_calendar_events', 'academic_calendar_events_event_type_index')) {
            Schema::table('academic_calendar_events', function (Blueprint $table) {
                $table->index('event_type', 'academic_calendar_events_event_type_index');
            });
        }
    }

    private function dropIndexIfExists($tableName, $indexName)
    {
        if (!Schema::hasTable($tableName) || !$this->indexExists($tableName, $indexName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($indexName) {
            $table->dropIndex($indexName);
        });
    }

    private function indexExists($tableName, $indexName)
    {
        if (DB::getDriverName() !== 'mysql') {
            return false;
        }

        $databaseName = DB::getDatabaseName();
        if (!$databaseName) {
            return false;
        }

        return DB::table('information_schema.statistics')
            ->where('table_schema', $databaseName)
            ->where('table_name', $tableName)
            ->where('index_name', $indexName)
            ->exists();
    }
}
