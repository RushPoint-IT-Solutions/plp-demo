<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WaveCCanonicalizeAcademicTerms extends Migration
{
    private $tableTermMap = [
        'alumni_tracker_settings' => ['school_year', 'term'],
        'applicant_application_preferences' => ['school_year', 'semester'],
        'bed_days' => ['school_year', 'semester'],
        'bed_student_statuses' => ['school_year', 'term'],
        'cancellation_waivers' => ['school_year', 'semester'],
        'certificates_issued' => ['school_year', 'semester'],
        'cross_enrollment_requests' => ['school_year', 'semester'],
        'grading_components' => ['school_year', 'semester'],
        'grading_periods' => ['school_year', 'semester'],
        'students' => ['school_year', 'semester'],
        'student_update_runs' => ['school_year', 'term'],
        'subjects' => ['school_year', 'semester'],
        'system_grade_postings' => ['school_year', 'semester'],
        'system_school_semesters' => ['school_year', 'semester'],
        'transmutation_rules' => ['school_year', 'term'],
    ];

    public function up()
    {
        $this->createAcademicTermsTable();
        $this->addAcademicTermColumns();
        $this->backfillAcademicTerms();
        $this->addAcademicTermForeignKeys();
    }

    public function down()
    {
        $this->dropAcademicTermForeignKeys();
        $this->dropAcademicTermColumns();

        if (Schema::hasTable('academic_terms')) {
            Schema::drop('academic_terms');
        }
    }

    private function createAcademicTermsTable()
    {
        if (Schema::hasTable('academic_terms')) {
            return;
        }

        Schema::create('academic_terms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('school_year', 30);
            $table->string('term', 40);
            $table->string('canonical_key', 100)->unique();
            $table->timestamps();

            $table->index(['school_year', 'term'], 'academic_terms_school_year_term_idx');
        });
    }

    private function addAcademicTermColumns()
    {
        foreach ($this->tableTermMap as $tableName => $columns) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            if (!Schema::hasColumn($tableName, $columns[0]) || !Schema::hasColumn($tableName, $columns[1])) {
                continue;
            }

            if (!Schema::hasColumn($tableName, 'academic_term_id')) {
                Schema::table($tableName, function (Blueprint $table) use ($columns, $tableName) {
                    $table->unsignedBigInteger('academic_term_id')->nullable()->after($columns[1]);

                    $indexName = $tableName . '_academic_term_id_idx';
                    if (!$this->indexExists($tableName, $indexName)) {
                        $table->index('academic_term_id', $indexName);
                    }
                });
            }
        }
    }

    private function backfillAcademicTerms()
    {
        foreach ($this->tableTermMap as $tableName => $columns) {
            if (!Schema::hasTable($tableName)
                || !Schema::hasColumn($tableName, $columns[0])
                || !Schema::hasColumn($tableName, $columns[1])
                || !Schema::hasColumn($tableName, 'academic_term_id')) {
                continue;
            }

            $pairs = DB::table($tableName)
                ->select($columns[0] . ' as school_year_raw', $columns[1] . ' as term_raw')
                ->whereNotNull($columns[0])
                ->whereNotNull($columns[1])
                ->where($columns[0], '<>', '')
                ->where($columns[1], '<>', '')
                ->distinct()
                ->get();

            foreach ($pairs as $pair) {
                $schoolYear = trim((string) $pair->school_year_raw);
                $term = trim((string) $pair->term_raw);

                if ($schoolYear === '' || $term === '') {
                    continue;
                }

                $academicTermId = $this->resolveAcademicTermId($schoolYear, $term);

                DB::table($tableName)
                    ->whereNull('academic_term_id')
                    ->where($columns[0], $pair->school_year_raw)
                    ->where($columns[1], $pair->term_raw)
                    ->update(['academic_term_id' => $academicTermId]);
            }
        }
    }

    private function addAcademicTermForeignKeys()
    {
        foreach ($this->tableTermMap as $tableName => $columns) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'academic_term_id')) {
                continue;
            }

            $foreignName = $tableName . '_academic_term_id_foreign';
            if ($this->foreignKeyExists($tableName, $foreignName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($foreignName) {
                $table->foreign('academic_term_id', $foreignName)
                    ->references('id')
                    ->on('academic_terms')
                    ->onDelete('set null');
            });
        }
    }

    private function dropAcademicTermForeignKeys()
    {
        foreach ($this->tableTermMap as $tableName => $columns) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'academic_term_id')) {
                continue;
            }

            $foreignName = $tableName . '_academic_term_id_foreign';
            if (!$this->foreignKeyExists($tableName, $foreignName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($foreignName) {
                $table->dropForeign($foreignName);
            });
        }
    }

    private function dropAcademicTermColumns()
    {
        foreach ($this->tableTermMap as $tableName => $columns) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'academic_term_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $indexName = $tableName . '_academic_term_id_idx';
                if ($this->indexExists($tableName, $indexName)) {
                    $table->dropIndex($indexName);
                }

                $table->dropColumn('academic_term_id');
            });
        }
    }

    private function resolveAcademicTermId($schoolYear, $term)
    {
        $canonical = $this->canonicalKey($schoolYear, $term);

        $existing = DB::table('academic_terms')
            ->where('canonical_key', $canonical)
            ->first();

        if ($existing) {
            return (int) $existing->id;
        }

        DB::table('academic_terms')->insert([
            'school_year' => $schoolYear,
            'term' => $term,
            'canonical_key' => $canonical,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return (int) DB::getPdo()->lastInsertId();
    }

    private function canonicalKey($schoolYear, $term)
    {
        return strtolower(trim((string) $schoolYear) . '|' . trim((string) $term));
    }

    private function foreignKeyExists($tableName, $foreignKeyName)
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
            ->where('constraint_name', $foreignKeyName)
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
