<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WaveHStrictifyProgramYearDimensions extends Migration
{
    private function definitions()
    {
        return [
            ['table' => 'students', 'course_column' => 'program', 'year_column' => 'year_level'],
            ['table' => 'cancellation_waivers', 'course_column' => 'program', 'year_column' => 'year_level', 'student_fk' => 'student_id'],
            ['table' => 'cross_enrollment_requests', 'course_column' => 'program', 'year_column' => 'year_level', 'student_fk' => 'student_id'],
            ['table' => 'master_student_profiles', 'course_column' => 'course', 'year_column' => 'year_level', 'student_fk' => 'source_student_id'],
            ['table' => 'master_student_grade_files', 'course_column' => 'course', 'year_column' => 'year_level', 'student_fk' => 'source_student_id'],
            ['table' => 'bed_student_statuses', 'course_column' => 'course', 'year_column' => 'year_level'],
            ['table' => 'student_update_runs', 'course_column' => 'course', 'year_column' => 'year_level'],
        ];
    }

    public function up()
    {
        $this->backfillDimensionIdsFromLegacyColumns();
        $this->backfillDimensionIdsFromStudents();
        $this->dropLegacyDimensionColumns();
    }

    public function down()
    {
        $this->addLegacyDimensionColumns();
        $this->restoreLegacyDimensionValues();
    }

    private function backfillDimensionIdsFromLegacyColumns()
    {
        foreach ($this->definitions() as $definition) {
            $table = $definition['table'];
            $courseColumn = $definition['course_column'];
            $yearColumn = $definition['year_column'];

            if (!Schema::hasTable($table)) {
                continue;
            }

            if (Schema::hasColumn($table, $courseColumn) && Schema::hasColumn($table, 'course_id')) {
                DB::statement(
                    "UPDATE `{$table}` t
                    LEFT JOIN `courses` c
                        ON LOWER(TRIM(c.name)) = LOWER(TRIM(t.`{$courseColumn}`))
                        OR LOWER(TRIM(c.code)) = LOWER(TRIM(t.`{$courseColumn}`))
                    SET t.course_id = c.id
                    WHERE t.course_id IS NULL
                        AND t.`{$courseColumn}` IS NOT NULL
                        AND TRIM(t.`{$courseColumn}`) <> ''
                        AND c.id IS NOT NULL"
                );
            }

            if (Schema::hasColumn($table, $yearColumn) && Schema::hasColumn($table, 'year_block_id')) {
                $yearCase = $this->normalizedYearBlockCase("t.`{$yearColumn}`");

                DB::statement(
                    "UPDATE `{$table}` t
                    LEFT JOIN `year_blocks` y ON y.label = {$yearCase}
                    SET t.year_block_id = y.id
                    WHERE t.year_block_id IS NULL
                        AND t.`{$yearColumn}` IS NOT NULL
                        AND TRIM(t.`{$yearColumn}`) <> ''
                        AND y.id IS NOT NULL"
                );
            }
        }
    }

    private function backfillDimensionIdsFromStudents()
    {
        foreach ($this->definitions() as $definition) {
            if (empty($definition['student_fk'])) {
                continue;
            }

            $table = $definition['table'];
            $studentFk = $definition['student_fk'];

            if (!Schema::hasTable($table)
                || !Schema::hasTable('students')
                || !Schema::hasColumn($table, $studentFk)
                || !Schema::hasColumn($table, 'course_id')
                || !Schema::hasColumn($table, 'year_block_id')
                || !Schema::hasColumn('students', 'course_id')
                || !Schema::hasColumn('students', 'year_block_id')) {
                continue;
            }

            DB::statement(
                "UPDATE `{$table}` t
                JOIN `students` s ON s.id = t.`{$studentFk}`
                SET
                    t.course_id = COALESCE(t.course_id, s.course_id),
                    t.year_block_id = COALESCE(t.year_block_id, s.year_block_id)
                WHERE t.`{$studentFk}` IS NOT NULL"
            );
        }
    }

    private function dropLegacyDimensionColumns()
    {
        foreach ($this->definitions() as $definition) {
            $table = $definition['table'];
            $courseColumn = $definition['course_column'];
            $yearColumn = $definition['year_column'];

            if (!Schema::hasTable($table)) {
                continue;
            }

            $columnsToDrop = [];

            if (Schema::hasColumn($table, $courseColumn)) {
                $columnsToDrop[] = $courseColumn;
            }

            if (Schema::hasColumn($table, $yearColumn)) {
                $columnsToDrop[] = $yearColumn;
            }

            if (!count($columnsToDrop)) {
                continue;
            }

            Schema::table($table, function (Blueprint $tableBlueprint) use ($columnsToDrop) {
                $tableBlueprint->dropColumn($columnsToDrop);
            });
        }
    }

    private function addLegacyDimensionColumns()
    {
        foreach ($this->definitions() as $definition) {
            $table = $definition['table'];
            $courseColumn = $definition['course_column'];
            $yearColumn = $definition['year_column'];

            if (!Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $tableBlueprint) use ($table, $courseColumn, $yearColumn) {
                if (!Schema::hasColumn($table, $courseColumn)) {
                    $tableBlueprint->string($courseColumn, 191)->nullable()->after('course_id');
                }

                if (!Schema::hasColumn($table, $yearColumn)) {
                    $tableBlueprint->string($yearColumn, 60)->nullable()->after('year_block_id');
                }
            });
        }
    }

    private function restoreLegacyDimensionValues()
    {
        foreach ($this->definitions() as $definition) {
            $table = $definition['table'];
            $courseColumn = $definition['course_column'];
            $yearColumn = $definition['year_column'];

            if (!Schema::hasTable($table)
                || !Schema::hasColumn($table, $courseColumn)
                || !Schema::hasColumn($table, $yearColumn)
                || !Schema::hasColumn($table, 'course_id')
                || !Schema::hasColumn($table, 'year_block_id')) {
                continue;
            }

            DB::statement(
                "UPDATE `{$table}` t
                LEFT JOIN `courses` c ON c.id = t.course_id
                LEFT JOIN `year_blocks` y ON y.id = t.year_block_id
                SET
                    t.`{$courseColumn}` = COALESCE(NULLIF(TRIM(t.`{$courseColumn}`), ''), COALESCE(NULLIF(c.code, ''), c.name)),
                    t.`{$yearColumn}` = COALESCE(NULLIF(TRIM(t.`{$yearColumn}`), ''), y.label)
                WHERE t.course_id IS NOT NULL OR t.year_block_id IS NOT NULL"
            );
        }
    }

    private function normalizedYearBlockCase($columnExpression)
    {
        return "(
            CASE
                WHEN LOWER(TRIM({$columnExpression})) IN ('1', '1st', '1st year', 'first', 'first year', '1st yr') THEN '1st Year'
                WHEN LOWER(TRIM({$columnExpression})) IN ('2', '2nd', '2nd year', 'second', 'second year', '2nd yr') THEN '2nd Year'
                WHEN LOWER(TRIM({$columnExpression})) IN ('3', '3rd', '3rd year', 'third', 'third year', '3rd yr') THEN '3rd Year'
                WHEN LOWER(TRIM({$columnExpression})) IN ('4', '4th', '4th year', 'fourth', 'fourth year', '4th yr', '4a') THEN '4th Year'
                WHEN LOWER(TRIM({$columnExpression})) LIKE '1%' THEN '1st Year'
                WHEN LOWER(TRIM({$columnExpression})) LIKE '2%' THEN '2nd Year'
                WHEN LOWER(TRIM({$columnExpression})) LIKE '3%' THEN '3rd Year'
                WHEN LOWER(TRIM({$columnExpression})) LIKE '4%' THEN '4th Year'
                ELSE NULL
            END
        )";
    }
}
