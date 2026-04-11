<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WaveIStrictifyCourseProgramDimensions extends Migration
{
    private function definitions()
    {
        return [
            [
                'table' => 'subjects',
                'legacy_column' => 'course',
                'id_column' => 'course_id',
                'length' => 191,
                'index_name' => 'subjects_course_id_idx',
            ],
            [
                'table' => 'transmutation_rules',
                'legacy_column' => 'program',
                'id_column' => 'course_id',
                'length' => 50,
                'index_name' => 'transmutation_rules_course_id_idx',
                'legacy_index' => 'transmutation_rules_program_index',
            ],
            [
                'table' => 'system_announcements',
                'legacy_column' => 'program',
                'id_column' => 'course_id',
                'length' => 120,
                'index_name' => 'system_announcements_course_id_idx',
            ],
        ];
    }

    public function up()
    {
        $this->addCourseIdColumns();
        $this->backfillCourseIdsFromLegacyColumns();
        $this->dropLegacyCourseProgramColumns();
        $this->addForeignKeys();
    }

    public function down()
    {
        $this->dropForeignKeys();
        $this->addLegacyCourseProgramColumns();
        $this->restoreLegacyCourseProgramValues();
        $this->restoreLegacyIndexes();
        $this->dropCourseIdColumns();
    }

    private function addCourseIdColumns()
    {
        foreach ($this->definitions() as $definition) {
            $table = $definition['table'];
            $legacyColumn = $definition['legacy_column'];
            $idColumn = $definition['id_column'];
            $indexName = $definition['index_name'];

            if (!Schema::hasTable($table) || !Schema::hasTable('courses') || Schema::hasColumn($table, $idColumn)) {
                continue;
            }

            Schema::table($table, function (Blueprint $tableBlueprint) use ($table, $legacyColumn, $idColumn) {
                if (Schema::hasColumn($table, $legacyColumn)) {
                    $tableBlueprint->unsignedBigInteger($idColumn)->nullable()->after($legacyColumn);
                } else {
                    $tableBlueprint->unsignedBigInteger($idColumn)->nullable();
                }
            });

            if (!$this->indexExists($table, $indexName)) {
                Schema::table($table, function (Blueprint $tableBlueprint) use ($idColumn, $indexName) {
                    $tableBlueprint->index($idColumn, $indexName);
                });
            }
        }
    }

    private function backfillCourseIdsFromLegacyColumns()
    {
        foreach ($this->definitions() as $definition) {
            $table = $definition['table'];
            $legacyColumn = $definition['legacy_column'];
            $idColumn = $definition['id_column'];

            if (!Schema::hasTable($table)
                || !Schema::hasTable('courses')
                || !Schema::hasColumn($table, $legacyColumn)
                || !Schema::hasColumn($table, $idColumn)) {
                continue;
            }

            DB::statement(
                "UPDATE `{$table}` t
                LEFT JOIN `courses` c
                    ON LOWER(TRIM(c.code)) = LOWER(TRIM(t.`{$legacyColumn}`))
                    OR LOWER(TRIM(c.name)) = LOWER(TRIM(t.`{$legacyColumn}`))
                SET t.`{$idColumn}` = c.id
                WHERE t.`{$idColumn}` IS NULL
                    AND t.`{$legacyColumn}` IS NOT NULL
                    AND TRIM(t.`{$legacyColumn}`) <> ''
                    AND c.id IS NOT NULL"
            );
        }
    }

    private function dropLegacyCourseProgramColumns()
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

    private function addForeignKeys()
    {
        foreach ($this->definitions() as $definition) {
            $table = $definition['table'];
            $idColumn = $definition['id_column'];
            $foreignName = $table . '_' . $idColumn . '_foreign';

            if (!Schema::hasTable($table)
                || !Schema::hasTable('courses')
                || !Schema::hasColumn($table, $idColumn)
                || $this->foreignKeyExists($table, $foreignName)) {
                continue;
            }

            Schema::table($table, function (Blueprint $tableBlueprint) use ($idColumn, $foreignName) {
                $tableBlueprint->foreign($idColumn, $foreignName)
                    ->references('id')
                    ->on('courses')
                    ->onDelete('set null');
            });
        }
    }

    private function dropForeignKeys()
    {
        foreach ($this->definitions() as $definition) {
            $table = $definition['table'];
            $idColumn = $definition['id_column'];
            $foreignName = $table . '_' . $idColumn . '_foreign';

            if (!Schema::hasTable($table) || !$this->foreignKeyExists($table, $foreignName)) {
                continue;
            }

            Schema::table($table, function (Blueprint $tableBlueprint) use ($foreignName) {
                $tableBlueprint->dropForeign($foreignName);
            });
        }
    }

    private function addLegacyCourseProgramColumns()
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

    private function restoreLegacyCourseProgramValues()
    {
        foreach ($this->definitions() as $definition) {
            $table = $definition['table'];
            $legacyColumn = $definition['legacy_column'];
            $idColumn = $definition['id_column'];

            if (!Schema::hasTable($table)
                || !Schema::hasTable('courses')
                || !Schema::hasColumn($table, $legacyColumn)
                || !Schema::hasColumn($table, $idColumn)) {
                continue;
            }

            DB::statement(
                "UPDATE `{$table}` t
                LEFT JOIN `courses` c ON c.id = t.`{$idColumn}`
                SET t.`{$legacyColumn}` = COALESCE(NULLIF(TRIM(t.`{$legacyColumn}`), ''), COALESCE(NULLIF(c.code, ''), c.name))
                WHERE t.`{$idColumn}` IS NOT NULL"
            );
        }
    }

    private function restoreLegacyIndexes()
    {
        if (Schema::hasTable('transmutation_rules')
            && Schema::hasColumn('transmutation_rules', 'program')
            && !$this->indexExists('transmutation_rules', 'transmutation_rules_program_index')) {
            Schema::table('transmutation_rules', function (Blueprint $table) {
                $table->index('program', 'transmutation_rules_program_index');
            });
        }
    }

    private function dropCourseIdColumns()
    {
        foreach ($this->definitions() as $definition) {
            $table = $definition['table'];
            $idColumn = $definition['id_column'];
            $indexName = $definition['index_name'];

            if (!Schema::hasTable($table) || !Schema::hasColumn($table, $idColumn)) {
                continue;
            }

            $this->dropIndexIfExists($table, $indexName);

            Schema::table($table, function (Blueprint $tableBlueprint) use ($idColumn) {
                $tableBlueprint->dropColumn($idColumn);
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