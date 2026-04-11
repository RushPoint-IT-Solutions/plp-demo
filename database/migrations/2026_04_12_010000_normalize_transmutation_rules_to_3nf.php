<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NormalizeTransmutationRulesTo3nf extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('transmutation_rules')) {
            return;
        }

        if (!Schema::hasColumn('transmutation_rules', 'academic_term_id')) {
            Schema::table('transmutation_rules', function (Blueprint $table) {
                $table->unsignedBigInteger('academic_term_id')->nullable()->after('id');
            });
        }

        if (!Schema::hasColumn('transmutation_rules', 'course_id')) {
            Schema::table('transmutation_rules', function (Blueprint $table) {
                $table->unsignedBigInteger('course_id')->nullable()->after('academic_term_id');
            });
        }

        $this->backfillAcademicTermFromLegacyColumns();
        $this->backfillCourseFromLegacyProgram();

        if (!$this->indexExists('transmutation_rules', 'transmutation_rules_academic_term_id_idx')) {
            Schema::table('transmutation_rules', function (Blueprint $table) {
                $table->index('academic_term_id', 'transmutation_rules_academic_term_id_idx');
            });
        }

        if (!$this->indexExists('transmutation_rules', 'transmutation_rules_course_id_idx')) {
            Schema::table('transmutation_rules', function (Blueprint $table) {
                $table->index('course_id', 'transmutation_rules_course_id_idx');
            });
        }

        if (!$this->foreignKeyExistsForColumn('transmutation_rules', 'academic_term_id')) {
            Schema::table('transmutation_rules', function (Blueprint $table) {
                $table->foreign('academic_term_id', 'transmutation_rules_academic_term_fk')
                    ->references('id')
                    ->on('academic_terms')
                    ->onDelete('set null');
            });
        }

        if (!$this->foreignKeyExistsForColumn('transmutation_rules', 'course_id')) {
            Schema::table('transmutation_rules', function (Blueprint $table) {
                $table->foreign('course_id', 'transmutation_rules_course_fk')
                    ->references('id')
                    ->on('courses')
                    ->onDelete('set null');
            });
        }

        $this->deduplicateRowsByBand();

        if (!$this->indexExists('transmutation_rules', 'transmutation_rules_term_course_band_uniq')) {
            Schema::table('transmutation_rules', function (Blueprint $table) {
                $table->unique(
                    ['academic_term_id', 'course_id', 'initial_from', 'initial_to'],
                    'transmutation_rules_term_course_band_uniq'
                );
            });
        }

        $columnsToDrop = [];
        foreach (['school_year', 'term', 'program'] as $legacyColumn) {
            if (Schema::hasColumn('transmutation_rules', $legacyColumn)) {
                $columnsToDrop[] = $legacyColumn;
            }
        }

        if (count($columnsToDrop)) {
            Schema::table('transmutation_rules', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }
    }

    public function down()
    {
        if (!Schema::hasTable('transmutation_rules')) {
            return;
        }

        if (!Schema::hasColumn('transmutation_rules', 'school_year')) {
            Schema::table('transmutation_rules', function (Blueprint $table) {
                $table->string('school_year', 20)->nullable()->after('course_id');
            });
        }

        if (!Schema::hasColumn('transmutation_rules', 'term')) {
            Schema::table('transmutation_rules', function (Blueprint $table) {
                $table->string('term', 20)->nullable()->after('school_year');
            });
        }

        if (!Schema::hasColumn('transmutation_rules', 'program')) {
            Schema::table('transmutation_rules', function (Blueprint $table) {
                $table->string('program', 50)->nullable()->after('term');
            });
        }

        if (Schema::hasColumn('transmutation_rules', 'academic_term_id')) {
            DB::statement(
                "UPDATE transmutation_rules tr
                LEFT JOIN academic_terms at ON at.id = tr.academic_term_id
                SET tr.school_year = COALESCE(tr.school_year, at.school_year),
                    tr.term = COALESCE(tr.term, at.term)
                WHERE tr.academic_term_id IS NOT NULL"
            );
        }

        if (Schema::hasColumn('transmutation_rules', 'course_id')) {
            DB::statement(
                "UPDATE transmutation_rules tr
                LEFT JOIN courses c ON c.id = tr.course_id
                SET tr.program = COALESCE(tr.program, NULLIF(c.code, ''), c.name)
                WHERE tr.course_id IS NOT NULL"
            );
        }

        if ($this->indexExists('transmutation_rules', 'transmutation_rules_term_course_band_uniq')) {
            Schema::table('transmutation_rules', function (Blueprint $table) {
                $table->dropUnique('transmutation_rules_term_course_band_uniq');
            });
        }

        if ($this->foreignKeyExists('transmutation_rules', 'transmutation_rules_academic_term_fk')) {
            Schema::table('transmutation_rules', function (Blueprint $table) {
                $table->dropForeign('transmutation_rules_academic_term_fk');
            });
        }

        if ($this->foreignKeyExists('transmutation_rules', 'transmutation_rules_course_fk')) {
            Schema::table('transmutation_rules', function (Blueprint $table) {
                $table->dropForeign('transmutation_rules_course_fk');
            });
        }
    }

    private function backfillAcademicTermFromLegacyColumns()
    {
        if (!Schema::hasColumn('transmutation_rules', 'school_year')
            || !Schema::hasColumn('transmutation_rules', 'term')
            || !Schema::hasColumn('transmutation_rules', 'academic_term_id')) {
            return;
        }

        DB::statement(
            "UPDATE transmutation_rules tr
            INNER JOIN academic_terms at
                ON at.school_year = tr.school_year
                AND LOWER(TRIM(at.term)) = LOWER(TRIM(tr.term))
            SET tr.academic_term_id = at.id
            WHERE tr.academic_term_id IS NULL
                AND tr.school_year IS NOT NULL
                AND tr.school_year <> ''
                AND tr.term IS NOT NULL
                AND tr.term <> ''"
        );
    }

    private function backfillCourseFromLegacyProgram()
    {
        if (!Schema::hasColumn('transmutation_rules', 'program')
            || !Schema::hasColumn('transmutation_rules', 'course_id')) {
            return;
        }

        DB::statement(
            "UPDATE transmutation_rules tr
            INNER JOIN courses c
                ON LOWER(TRIM(c.code)) = LOWER(TRIM(tr.program))
                OR LOWER(TRIM(c.name)) = LOWER(TRIM(tr.program))
            SET tr.course_id = c.id
            WHERE tr.course_id IS NULL
                AND tr.program IS NOT NULL
                AND tr.program <> ''"
        );
    }

    private function deduplicateRowsByBand()
    {
        if (!Schema::hasColumn('transmutation_rules', 'academic_term_id')
            || !Schema::hasColumn('transmutation_rules', 'course_id')) {
            return;
        }

        DB::statement(
            "DELETE tr_keep FROM transmutation_rules tr_keep
            INNER JOIN transmutation_rules tr_drop
                ON tr_keep.id > tr_drop.id
                AND COALESCE(tr_keep.academic_term_id, 0) = COALESCE(tr_drop.academic_term_id, 0)
                AND COALESCE(tr_keep.course_id, 0) = COALESCE(tr_drop.course_id, 0)
                AND tr_keep.initial_from = tr_drop.initial_from
                AND tr_keep.initial_to = tr_drop.initial_to"
        );
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

    private function foreignKeyExistsForColumn($tableName, $columnName)
    {
        if (DB::getDriverName() !== 'mysql') {
            return false;
        }

        $databaseName = DB::getDatabaseName();
        if (!$databaseName) {
            return false;
        }

        return DB::table('information_schema.key_column_usage')
            ->where('table_schema', $databaseName)
            ->where('table_name', $tableName)
            ->where('column_name', $columnName)
            ->whereNotNull('referenced_table_name')
            ->exists();
    }
}
