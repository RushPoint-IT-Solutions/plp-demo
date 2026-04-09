<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WaveAExpandStatusTypeLookups extends Migration
{
    public function up()
    {
        $this->createLookupTables();
        $this->addForeignKeyColumns();
        $this->backfillLookupReferences();
        $this->addForeignKeys();
    }

    public function down()
    {
        $this->dropForeignKeys();
        $this->dropForeignKeyColumns();
        $this->dropLookupTables();
    }

    private function createLookupTables()
    {
        $this->createLookupTableIfMissing('applicant_application_statuses');
        $this->createLookupTableIfMissing('applicant_exam_result_statuses');
        $this->createLookupTableIfMissing('academic_event_types');
        $this->createLookupTableIfMissing('waiver_statuses');
        $this->createLookupTableIfMissing('cross_enrollment_statuses');
        $this->createLookupTableIfMissing('subject_load_types');
    }

    private function createLookupTableIfMissing($tableName)
    {
        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code', 120)->unique();
            $table->string('label', 120)->nullable();
            $table->timestamps();
        });
    }

    private function addForeignKeyColumns()
    {
        $this->addLookupColumn('applicants', 'application_status', 'application_status_id', 'applicant_application_statuses');
        $this->addLookupColumn('applicants', 'exam_result_status', 'exam_result_status_id', 'applicant_exam_result_statuses');
        $this->addLookupColumn('academic_calendar_events', 'event_type', 'event_type_id', 'academic_event_types');
        $this->addLookupColumn('cancellation_waivers', 'status', 'status_id', 'waiver_statuses');
        $this->addLookupColumn('cross_enrollment_requests', 'status', 'status_id', 'cross_enrollment_statuses');
        $this->addLookupColumn('subjects', 'load_type', 'load_type_id', 'subject_load_types');
    }

    private function addLookupColumn($sourceTable, $legacyColumn, $foreignKeyColumn, $lookupTable)
    {
        if (!Schema::hasTable($sourceTable)
            || !Schema::hasTable($lookupTable)
            || !Schema::hasColumn($sourceTable, $legacyColumn)
            || Schema::hasColumn($sourceTable, $foreignKeyColumn)) {
            return;
        }

        Schema::table($sourceTable, function (Blueprint $table) use ($legacyColumn, $foreignKeyColumn, $sourceTable) {
            $table->unsignedBigInteger($foreignKeyColumn)->nullable()->after($legacyColumn);
            $table->index($foreignKeyColumn, $sourceTable . '_' . $foreignKeyColumn . '_idx');
        });
    }

    private function backfillLookupReferences()
    {
        $this->backfillSingleMapping('applicants', 'application_status', 'applicant_application_statuses', 'application_status_id');
        $this->backfillSingleMapping('applicants', 'exam_result_status', 'applicant_exam_result_statuses', 'exam_result_status_id');
        $this->backfillSingleMapping('academic_calendar_events', 'event_type', 'academic_event_types', 'event_type_id');
        $this->backfillSingleMapping('cancellation_waivers', 'status', 'waiver_statuses', 'status_id');
        $this->backfillSingleMapping('cross_enrollment_requests', 'status', 'cross_enrollment_statuses', 'status_id');
        $this->backfillSingleMapping('subjects', 'load_type', 'subject_load_types', 'load_type_id');
    }

    private function addForeignKeys()
    {
        $this->addForeignKey('applicants', 'application_status_id', 'applicant_application_statuses');
        $this->addForeignKey('applicants', 'exam_result_status_id', 'applicant_exam_result_statuses');
        $this->addForeignKey('academic_calendar_events', 'event_type_id', 'academic_event_types');
        $this->addForeignKey('cancellation_waivers', 'status_id', 'waiver_statuses');
        $this->addForeignKey('cross_enrollment_requests', 'status_id', 'cross_enrollment_statuses');
        $this->addForeignKey('subjects', 'load_type_id', 'subject_load_types');
    }

    private function addForeignKey($sourceTable, $foreignKeyColumn, $lookupTable)
    {
        if (!Schema::hasTable($sourceTable)
            || !Schema::hasTable($lookupTable)
            || !Schema::hasColumn($sourceTable, $foreignKeyColumn)) {
            return;
        }

        $foreignName = $sourceTable . '_' . $foreignKeyColumn . '_foreign';
        if ($this->foreignKeyExists($sourceTable, $foreignName)) {
            return;
        }

        Schema::table($sourceTable, function (Blueprint $table) use ($foreignKeyColumn, $lookupTable, $foreignName) {
            $table->foreign($foreignKeyColumn, $foreignName)
                ->references('id')
                ->on($lookupTable)
                ->onDelete('set null');
        });
    }

    private function dropForeignKeys()
    {
        $this->dropForeignKeyIfExists('applicants', 'applicants_application_status_id_foreign');
        $this->dropForeignKeyIfExists('applicants', 'applicants_exam_result_status_id_foreign');
        $this->dropForeignKeyIfExists('academic_calendar_events', 'academic_calendar_events_event_type_id_foreign');
        $this->dropForeignKeyIfExists('cancellation_waivers', 'cancellation_waivers_status_id_foreign');
        $this->dropForeignKeyIfExists('cross_enrollment_requests', 'cross_enrollment_requests_status_id_foreign');
        $this->dropForeignKeyIfExists('subjects', 'subjects_load_type_id_foreign');
    }

    private function dropForeignKeyIfExists($tableName, $foreignName)
    {
        if (!Schema::hasTable($tableName) || !$this->foreignKeyExists($tableName, $foreignName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($foreignName) {
            $table->dropForeign($foreignName);
        });
    }

    private function dropForeignKeyColumns()
    {
        $this->dropLookupColumn('applicants', 'application_status_id');
        $this->dropLookupColumn('applicants', 'exam_result_status_id');
        $this->dropLookupColumn('academic_calendar_events', 'event_type_id');
        $this->dropLookupColumn('cancellation_waivers', 'status_id');
        $this->dropLookupColumn('cross_enrollment_requests', 'status_id');
        $this->dropLookupColumn('subjects', 'load_type_id');
    }

    private function dropLookupColumn($sourceTable, $foreignKeyColumn)
    {
        if (!Schema::hasTable($sourceTable) || !Schema::hasColumn($sourceTable, $foreignKeyColumn)) {
            return;
        }

        Schema::table($sourceTable, function (Blueprint $table) use ($sourceTable, $foreignKeyColumn) {
            $indexName = $sourceTable . '_' . $foreignKeyColumn . '_idx';
            if ($this->indexExists($sourceTable, $indexName)) {
                $table->dropIndex($indexName);
            }
            $table->dropColumn($foreignKeyColumn);
        });
    }

    private function dropLookupTables()
    {
        foreach ([
            'subject_load_types',
            'cross_enrollment_statuses',
            'waiver_statuses',
            'academic_event_types',
            'applicant_exam_result_statuses',
            'applicant_application_statuses',
        ] as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::drop($tableName);
            }
        }
    }

    private function backfillSingleMapping($sourceTable, $sourceColumn, $lookupTable, $foreignKeyColumn)
    {
        if (!Schema::hasTable($sourceTable)
            || !Schema::hasColumn($sourceTable, $sourceColumn)
            || !Schema::hasColumn($sourceTable, $foreignKeyColumn)
            || !Schema::hasTable($lookupTable)) {
            return;
        }

        $rawValues = DB::table($sourceTable)
            ->select($sourceColumn)
            ->whereNotNull($sourceColumn)
            ->where($sourceColumn, '<>', '')
            ->distinct()
            ->pluck($sourceColumn);

        foreach ($rawValues as $value) {
            $normalized = trim((string) $value);
            if ($normalized === '') {
                continue;
            }

            $existing = DB::table($lookupTable)
                ->where('code', $normalized)
                ->first();

            if (!$existing) {
                DB::table($lookupTable)->insert([
                    'code' => $normalized,
                    'label' => $normalized,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $lookupMap = DB::table($lookupTable)
            ->pluck('id', 'code')
            ->toArray();

        foreach ($lookupMap as $code => $lookupId) {
            DB::table($sourceTable)
                ->whereNull($foreignKeyColumn)
                ->where($sourceColumn, $code)
                ->update([$foreignKeyColumn => (int) $lookupId]);
        }
    }

    private function foreignKeyExists($tableName, $foreignName)
    {
        if (DB::getDriverName() !== 'mysql') {
            return false;
        }

        $databaseName = DB::getDatabaseName();
        if (!$databaseName) {
            return false;
        }

        return DB::table('information_schema.table_constraints')
            ->where('constraint_schema', $databaseName)
            ->where('table_name', $tableName)
            ->where('constraint_name', $foreignName)
            ->where('constraint_type', 'FOREIGN KEY')
            ->exists();
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
