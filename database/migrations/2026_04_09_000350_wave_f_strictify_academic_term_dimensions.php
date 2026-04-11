<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WaveFStrictifyAcademicTermDimensions extends Migration
{
    private $tableTermMap = [
        'alumni_tracker_settings' => 'term',
        'applicant_application_preferences' => 'semester',
        'bed_days' => 'semester',
        'bed_student_statuses' => 'term',
        'cancellation_waivers' => 'semester',
        'certificates_issued' => 'semester',
        'cross_enrollment_requests' => 'semester',
        'grading_components' => 'semester',
        'grading_periods' => 'semester',
        'students' => 'semester',
        'student_update_runs' => 'term',
        'subjects' => 'semester',
        'system_grade_postings' => 'semester',
        'system_school_semesters' => 'semester',
        'transmutation_rules' => 'term',
    ];

    public function up()
    {
        $this->backfillAcademicTermIds();
        $this->dropLegacyAcademicTermColumns();
    }

    public function down()
    {
        $this->addLegacyAcademicTermColumns();
        $this->restoreLegacyAcademicTermValues();
        $this->restoreLegacyAcademicTermIndexes();
    }

    private function backfillAcademicTermIds()
    {
        foreach ($this->tableTermMap as $tableName => $termColumn) {
            if (!Schema::hasTable($tableName)
                || !Schema::hasColumn($tableName, 'academic_term_id')
                || !Schema::hasColumn($tableName, 'school_year')
                || !Schema::hasColumn($tableName, $termColumn)) {
                continue;
            }

            DB::statement(
                "UPDATE `{$tableName}` t
                LEFT JOIN `academic_terms` at
                    ON at.canonical_key = LOWER(CONCAT(TRIM(t.`school_year`), '|', TRIM(t.`{$termColumn}`)))
                SET t.academic_term_id = at.id
                WHERE t.academic_term_id IS NULL
                    AND t.`school_year` IS NOT NULL
                    AND TRIM(t.`school_year`) <> ''
                    AND t.`{$termColumn}` IS NOT NULL
                    AND TRIM(t.`{$termColumn}`) <> ''
                    AND at.id IS NOT NULL"
            );
        }
    }

    private function dropLegacyAcademicTermColumns()
    {
        foreach ($this->tableTermMap as $tableName => $termColumn) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            if ($tableName === 'grading_components') {
                $this->dropIndexIfExists($tableName, 'grading_components_school_year_semester_period_index');
            }

            if ($tableName === 'grading_periods') {
                $this->dropIndexIfExists($tableName, 'grading_periods_school_year_semester_index');
            }

            if ($tableName === 'transmutation_rules') {
                $this->dropIndexIfExists($tableName, 'transmutation_rules_school_year_term_index');
            }

            $columnsToDrop = [];

            if (Schema::hasColumn($tableName, 'school_year')) {
                $columnsToDrop[] = 'school_year';
            }

            if (Schema::hasColumn($tableName, $termColumn)) {
                $columnsToDrop[] = $termColumn;
            }

            if (!count($columnsToDrop)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }
    }

    private function addLegacyAcademicTermColumns()
    {
        foreach ($this->tableTermMap as $tableName => $termColumn) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName, $termColumn) {
                if (!Schema::hasColumn($tableName, 'school_year')) {
                    $table->string('school_year', 30)->nullable()->after('academic_term_id');
                }

                if (!Schema::hasColumn($tableName, $termColumn)) {
                    $table->string($termColumn, 40)->nullable()->after('school_year');
                }
            });
        }
    }

    private function restoreLegacyAcademicTermValues()
    {
        foreach ($this->tableTermMap as $tableName => $termColumn) {
            if (!Schema::hasTable($tableName)
                || !Schema::hasColumn($tableName, 'academic_term_id')
                || !Schema::hasColumn($tableName, 'school_year')
                || !Schema::hasColumn($tableName, $termColumn)) {
                continue;
            }

            DB::statement(
                "UPDATE `{$tableName}` t
                JOIN `academic_terms` at ON at.id = t.academic_term_id
                SET
                    t.`school_year` = COALESCE(NULLIF(TRIM(t.`school_year`), ''), at.school_year),
                    t.`{$termColumn}` = COALESCE(NULLIF(TRIM(t.`{$termColumn}`), ''), at.term)
                WHERE t.academic_term_id IS NOT NULL"
            );
        }
    }

    private function restoreLegacyAcademicTermIndexes()
    {
        if (Schema::hasTable('grading_components')
            && Schema::hasColumn('grading_components', 'school_year')
            && Schema::hasColumn('grading_components', 'semester')
            && !$this->indexExists('grading_components', 'grading_components_school_year_semester_period_index')) {
            Schema::table('grading_components', function (Blueprint $table) {
                $table->index(['school_year', 'semester', 'period'], 'grading_components_school_year_semester_period_index');
            });
        }

        if (Schema::hasTable('grading_periods')
            && Schema::hasColumn('grading_periods', 'school_year')
            && Schema::hasColumn('grading_periods', 'semester')
            && !$this->indexExists('grading_periods', 'grading_periods_school_year_semester_index')) {
            Schema::table('grading_periods', function (Blueprint $table) {
                $table->index(['school_year', 'semester'], 'grading_periods_school_year_semester_index');
            });
        }

        if (Schema::hasTable('transmutation_rules')
            && Schema::hasColumn('transmutation_rules', 'school_year')
            && Schema::hasColumn('transmutation_rules', 'term')
            && !$this->indexExists('transmutation_rules', 'transmutation_rules_school_year_term_index')) {
            Schema::table('transmutation_rules', function (Blueprint $table) {
                $table->index(['school_year', 'term'], 'transmutation_rules_school_year_term_index');
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
