<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NormalizeCurriculumYearsToLookupTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('curriculum_years')) {
            Schema::create('curriculum_years', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('code', 20)->unique();
                $table->string('label', 80)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        $this->seedCurriculumYearsFromCourseCurricula();

        if (Schema::hasTable('course_curricula') && !Schema::hasColumn('course_curricula', 'curriculum_year_id')) {
            Schema::table('course_curricula', function (Blueprint $table) {
                $table->unsignedBigInteger('curriculum_year_id')->nullable()->after('course_id');
            });
        }

        $this->backfillCourseCurriculaCurriculumYearIds();

        if (Schema::hasTable('course_curricula') && Schema::hasColumn('course_curricula', 'curriculum_year_id')) {
            if (!$this->indexExists('course_curricula', 'course_curricula_curriculum_year_id_idx')) {
                Schema::table('course_curricula', function (Blueprint $table) {
                    $table->index('curriculum_year_id', 'course_curricula_curriculum_year_id_idx');
                });
            }

            if (!$this->foreignKeyExists('course_curricula', 'course_curricula_curriculum_year_fk')) {
                Schema::table('course_curricula', function (Blueprint $table) {
                    $table->foreign('curriculum_year_id', 'course_curricula_curriculum_year_fk')
                        ->references('id')
                        ->on('curriculum_years')
                        ->onDelete('set null');
                });
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('course_curricula') && $this->foreignKeyExists('course_curricula', 'course_curricula_curriculum_year_fk')) {
            Schema::table('course_curricula', function (Blueprint $table) {
                $table->dropForeign('course_curricula_curriculum_year_fk');
            });
        }

        if (Schema::hasTable('course_curricula') && $this->indexExists('course_curricula', 'course_curricula_curriculum_year_id_idx')) {
            Schema::table('course_curricula', function (Blueprint $table) {
                $table->dropIndex('course_curricula_curriculum_year_id_idx');
            });
        }

        if (Schema::hasTable('course_curricula') && Schema::hasColumn('course_curricula', 'curriculum_year_id')) {
            Schema::table('course_curricula', function (Blueprint $table) {
                $table->dropColumn('curriculum_year_id');
            });
        }

        Schema::dropIfExists('curriculum_years');
    }

    private function seedCurriculumYearsFromCourseCurricula()
    {
        if (!Schema::hasTable('course_curricula') || !Schema::hasTable('curriculum_years')) {
            return;
        }

        $codes = DB::table('course_curricula')
            ->whereNotNull('curriculum_year_code')
            ->where('curriculum_year_code', '<>', '')
            ->distinct()
            ->pluck('curriculum_year_code');

        foreach ($codes as $code) {
            $yearCode = trim((string) $code);
            if ($yearCode === '') {
                continue;
            }

            DB::table('curriculum_years')->updateOrInsert(
                ['code' => $yearCode],
                [
                    'label' => $this->formatCurriculumYearLabel($yearCode),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function backfillCourseCurriculaCurriculumYearIds()
    {
        if (!Schema::hasTable('course_curricula')
            || !Schema::hasTable('curriculum_years')
            || !Schema::hasColumn('course_curricula', 'curriculum_year_id')) {
            return;
        }

        DB::statement(
            'UPDATE course_curricula cc
            INNER JOIN curriculum_years cy ON cy.code = cc.curriculum_year_code
            SET cc.curriculum_year_id = cy.id
            WHERE cc.curriculum_year_id IS NULL'
        );
    }

    private function formatCurriculumYearLabel($code)
    {
        $normalizedCode = trim((string) $code);

        if (preg_match('/^(\d{2})(\d{2})$/', $normalizedCode, $matches)) {
            $start = (int) $matches[1];
            $end = (int) $matches[2];
            $centuryBase = $start >= 80 ? 1900 : 2000;

            return 'AY ' . ($centuryBase + $start) . '-' . ($centuryBase + $end);
        }

        if (preg_match('/^(\d{4})-(\d{4})$/', $normalizedCode, $matches)) {
            return 'AY ' . $matches[1] . '-' . $matches[2];
        }

        return 'AY ' . $normalizedCode;
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
}
