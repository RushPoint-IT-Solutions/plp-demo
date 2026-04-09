<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WaveENormalizeStudentProgramYearDimensions extends Migration
{
    public function up()
    {
        $this->ensureCourseCatalogCoverage();
        $this->addDimensionColumns();
        $this->backfillDimensionColumns();
        $this->addDimensionForeignKeys();
    }

    public function down()
    {
        $this->dropDimensionForeignKeys();
        $this->dropDimensionColumns();
    }

    private function ensureCourseCatalogCoverage()
    {
        if (!Schema::hasTable('courses') || !Schema::hasTable('departments')) {
            return;
        }

        $departmentId = DB::table('departments')->where('code', 'CS')->value('id');
        if (!$departmentId) {
            $departmentId = DB::table('departments')->orderBy('id')->value('id');
        }

        if (!$departmentId) {
            return;
        }

        $requiredCourses = [
            [
                'code' => 'BSIS',
                'name' => 'Bachelor of Science in Information Systems',
            ],
            [
                'code' => 'BSCE',
                'name' => 'Bachelor of Science in Computer Engineering',
            ],
        ];

        foreach ($requiredCourses as $courseData) {
            $exists = DB::table('courses')
                ->where('code', $courseData['code'])
                ->orWhere('name', $courseData['name'])
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('courses')->insert([
                'code' => $courseData['code'],
                'name' => $courseData['name'],
                'program_type' => 'college',
                'department_id' => (int) $departmentId,
                'description' => $courseData['name'],
                'slots' => 0,
                'track_category' => null,
                'non_filipino' => false,
                'dean_director_id' => null,
                'program_file' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function addDimensionColumns()
    {
        foreach ($this->tableDefinitions() as $definition) {
            $this->addDimensionColumnsForTable($definition);
        }
    }

    private function addDimensionColumnsForTable(array $definition)
    {
        $tableName = $definition['table'];
        if (!Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($definition, $tableName) {
            if (!empty($definition['course_column'])
                && Schema::hasColumn($tableName, $definition['course_column'])
                && !Schema::hasColumn($tableName, 'course_id')) {
                $table->unsignedBigInteger('course_id')->nullable()->after($definition['course_column']);

                $indexName = $this->courseIndexName($tableName);
                if (!$this->indexExists($tableName, $indexName)) {
                    $table->index('course_id', $indexName);
                }
            }

            if (!empty($definition['year_column'])
                && Schema::hasColumn($tableName, $definition['year_column'])
                && !Schema::hasColumn($tableName, 'year_block_id')) {
                $table->unsignedBigInteger('year_block_id')->nullable()->after($definition['year_column']);

                $indexName = $this->yearBlockIndexName($tableName);
                if (!$this->indexExists($tableName, $indexName)) {
                    $table->index('year_block_id', $indexName);
                }
            }
        });
    }

    private function backfillDimensionColumns()
    {
        // Backfill students first so linked tables can inherit canonical values.
        $studentDefinition = $this->tableDefinitionByName('students');
        if ($studentDefinition) {
            $this->backfillDimensionsForTable($studentDefinition);
        }

        foreach ($this->tableDefinitions() as $definition) {
            if ($definition['table'] === 'students') {
                continue;
            }

            $this->backfillDimensionsForTable($definition);
        }

        $this->backfillFromStudentLinks('cancellation_waivers', 'student_id');
        $this->backfillFromStudentLinks('cross_enrollment_requests', 'student_id');
        $this->backfillFromStudentLinks('master_student_profiles', 'source_student_id');
        $this->backfillFromStudentLinks('master_student_grade_files', 'source_student_id');
    }

    private function backfillDimensionsForTable(array $definition)
    {
        $tableName = $definition['table'];
        if (!Schema::hasTable($tableName)) {
            return;
        }

        if (!empty($definition['course_column'])
            && Schema::hasColumn($tableName, $definition['course_column'])
            && Schema::hasColumn($tableName, 'course_id')) {
            $courseColumn = $definition['course_column'];

            DB::statement(
                "UPDATE `{$tableName}` t
                LEFT JOIN `courses` c
                    ON LOWER(TRIM(c.name)) = LOWER(TRIM(t.`{$courseColumn}`))
                    OR LOWER(TRIM(c.code)) = LOWER(TRIM(t.`{$courseColumn}`))
                SET t.course_id = c.id
                WHERE t.course_id IS NULL
                    AND t.`{$courseColumn}` IS NOT NULL
                    AND TRIM(t.`{$courseColumn}`) <> ''"
            );
        }

        if (!empty($definition['year_column'])
            && Schema::hasColumn($tableName, $definition['year_column'])
            && Schema::hasColumn($tableName, 'year_block_id')) {
            $yearColumn = $definition['year_column'];
            $yearCase = $this->normalizedYearCaseExpression("t.`{$yearColumn}`");

            DB::statement(
                "UPDATE `{$tableName}` t
                LEFT JOIN `year_blocks` y ON y.label = {$yearCase}
                SET t.year_block_id = y.id
                WHERE t.year_block_id IS NULL
                    AND t.`{$yearColumn}` IS NOT NULL
                    AND TRIM(t.`{$yearColumn}`) <> ''"
            );
        }
    }

    private function backfillFromStudentLinks($tableName, $studentForeignKey)
    {
        if (!Schema::hasTable($tableName)
            || !Schema::hasColumn($tableName, $studentForeignKey)
            || !Schema::hasColumn($tableName, 'course_id')
            || !Schema::hasColumn($tableName, 'year_block_id')
            || !Schema::hasTable('students')
            || !Schema::hasColumn('students', 'course_id')
            || !Schema::hasColumn('students', 'year_block_id')) {
            return;
        }

        DB::statement(
            "UPDATE `{$tableName}` t
            JOIN `students` s ON s.id = t.`{$studentForeignKey}`
            SET
                t.course_id = COALESCE(t.course_id, s.course_id),
                t.year_block_id = COALESCE(t.year_block_id, s.year_block_id)
            WHERE t.`{$studentForeignKey}` IS NOT NULL"
        );
    }

    private function addDimensionForeignKeys()
    {
        foreach ($this->tableDefinitions() as $definition) {
            $this->addDimensionForeignKeysForTable($definition);
        }
    }

    private function addDimensionForeignKeysForTable(array $definition)
    {
        $tableName = $definition['table'];
        if (!Schema::hasTable($tableName)) {
            return;
        }

        $courseForeign = $this->courseForeignName($tableName);
        if (Schema::hasColumn($tableName, 'course_id') && !$this->foreignKeyExists($tableName, $courseForeign)) {
            Schema::table($tableName, function (Blueprint $table) use ($courseForeign) {
                $table->foreign('course_id', $courseForeign)
                    ->references('id')
                    ->on('courses')
                    ->onDelete('set null');
            });
        }

        $yearForeign = $this->yearBlockForeignName($tableName);
        if (Schema::hasColumn($tableName, 'year_block_id') && !$this->foreignKeyExists($tableName, $yearForeign)) {
            Schema::table($tableName, function (Blueprint $table) use ($yearForeign) {
                $table->foreign('year_block_id', $yearForeign)
                    ->references('id')
                    ->on('year_blocks')
                    ->onDelete('set null');
            });
        }
    }

    private function dropDimensionForeignKeys()
    {
        foreach ($this->tableDefinitions() as $definition) {
            $tableName = $definition['table'];
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            $courseForeign = $this->courseForeignName($tableName);
            if (Schema::hasColumn($tableName, 'course_id') && $this->foreignKeyExists($tableName, $courseForeign)) {
                Schema::table($tableName, function (Blueprint $table) use ($courseForeign) {
                    $table->dropForeign($courseForeign);
                });
            }

            $yearForeign = $this->yearBlockForeignName($tableName);
            if (Schema::hasColumn($tableName, 'year_block_id') && $this->foreignKeyExists($tableName, $yearForeign)) {
                Schema::table($tableName, function (Blueprint $table) use ($yearForeign) {
                    $table->dropForeign($yearForeign);
                });
            }
        }
    }

    private function dropDimensionColumns()
    {
        foreach ($this->tableDefinitions() as $definition) {
            $this->dropDimensionColumnsForTable($definition['table']);
        }
    }

    private function dropDimensionColumnsForTable($tableName)
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            $columns = [];

            if (Schema::hasColumn($tableName, 'course_id')) {
                $indexName = $this->courseIndexName($tableName);
                if ($this->indexExists($tableName, $indexName)) {
                    $table->dropIndex($indexName);
                }
                $columns[] = 'course_id';
            }

            if (Schema::hasColumn($tableName, 'year_block_id')) {
                $indexName = $this->yearBlockIndexName($tableName);
                if ($this->indexExists($tableName, $indexName)) {
                    $table->dropIndex($indexName);
                }
                $columns[] = 'year_block_id';
            }

            if (count($columns)) {
                $table->dropColumn($columns);
            }
        });
    }

    private function normalizedYearCaseExpression($columnExpression)
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

    private function tableDefinitions()
    {
        return [
            ['table' => 'students', 'course_column' => 'program', 'year_column' => 'year_level'],
            ['table' => 'cancellation_waivers', 'course_column' => 'program', 'year_column' => 'year_level'],
            ['table' => 'cross_enrollment_requests', 'course_column' => 'program', 'year_column' => 'year_level'],
            ['table' => 'master_student_profiles', 'course_column' => 'course', 'year_column' => 'year_level'],
            ['table' => 'master_student_grade_files', 'course_column' => 'course', 'year_column' => 'year_level'],
            ['table' => 'bed_student_statuses', 'course_column' => 'course', 'year_column' => 'year_level'],
            ['table' => 'student_update_runs', 'course_column' => 'course', 'year_column' => 'year_level'],
        ];
    }

    private function tableDefinitionByName($tableName)
    {
        foreach ($this->tableDefinitions() as $definition) {
            if ($definition['table'] === $tableName) {
                return $definition;
            }
        }

        return null;
    }

    private function courseForeignName($tableName)
    {
        return $tableName . '_course_id_foreign';
    }

    private function yearBlockForeignName($tableName)
    {
        return $tableName . '_year_block_id_foreign';
    }

    private function courseIndexName($tableName)
    {
        return $tableName . '_course_id_idx';
    }

    private function yearBlockIndexName($tableName)
    {
        return $tableName . '_year_block_id_idx';
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
