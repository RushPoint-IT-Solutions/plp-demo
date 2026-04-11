<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WaveJNormalizeApplicantYearLevels extends Migration
{
    public function up()
    {
        $this->createLookupTable();
        $this->seedDefaultLookupValues();
        $this->seedLookupValuesFromLegacyColumn();
        $this->addYearLevelIdColumn();
        $this->backfillYearLevelIds();
        $this->addForeignKey();
        $this->dropLegacyYearLevelColumn();
    }

    public function down()
    {
        $this->dropForeignKey();
        $this->addLegacyYearLevelColumn();
        $this->restoreLegacyYearLevelValues();
        $this->dropYearLevelIdColumn();
        $this->dropLookupTable();
    }

    private function createLookupTable()
    {
        if (Schema::hasTable('applicant_year_levels')) {
            return;
        }

        Schema::create('applicant_year_levels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code', 120)->unique();
            $table->string('label', 120)->nullable();
            $table->timestamps();
        });
    }

    private function seedDefaultLookupValues()
    {
        if (!Schema::hasTable('applicant_year_levels')) {
            return;
        }

        $defaults = ['1st Year', '2nd Year', '3rd Year', '4th Year', 'Grade 11', 'Grade 12'];
        $now = now();

        foreach ($defaults as $value) {
            DB::table('applicant_year_levels')->updateOrInsert(
                ['code' => $value],
                ['label' => $value, 'updated_at' => $now, 'created_at' => $now]
            );
        }
    }

    private function seedLookupValuesFromLegacyColumn()
    {
        if (!Schema::hasTable('applicant_year_levels')
            || !Schema::hasTable('applicant_application_preferences')
            || !Schema::hasColumn('applicant_application_preferences', 'year_level')) {
            return;
        }

        $values = DB::table('applicant_application_preferences')
            ->select('year_level')
            ->whereNotNull('year_level')
            ->where('year_level', '<>', '')
            ->distinct()
            ->pluck('year_level');

        $now = now();
        foreach ($values as $value) {
            $normalized = trim((string) $value);
            if ($normalized === '') {
                continue;
            }

            DB::table('applicant_year_levels')->updateOrInsert(
                ['code' => $normalized],
                ['label' => $normalized, 'updated_at' => $now, 'created_at' => $now]
            );
        }
    }

    private function addYearLevelIdColumn()
    {
        if (!Schema::hasTable('applicant_application_preferences')
            || !Schema::hasTable('applicant_year_levels')
            || Schema::hasColumn('applicant_application_preferences', 'year_level_id')) {
            return;
        }

        Schema::table('applicant_application_preferences', function (Blueprint $table) {
            if (Schema::hasColumn('applicant_application_preferences', 'year_level')) {
                $table->unsignedBigInteger('year_level_id')->nullable()->after('year_level');
            } else {
                $table->unsignedBigInteger('year_level_id')->nullable()->after('apply_course_id');
            }

            $table->index('year_level_id', 'applicant_application_preferences_year_level_id_idx');
        });
    }

    private function backfillYearLevelIds()
    {
        if (!Schema::hasTable('applicant_application_preferences')
            || !Schema::hasTable('applicant_year_levels')
            || !Schema::hasColumn('applicant_application_preferences', 'year_level')
            || !Schema::hasColumn('applicant_application_preferences', 'year_level_id')) {
            return;
        }

        DB::statement(
            "UPDATE `applicant_application_preferences` p
            LEFT JOIN `applicant_year_levels` y
                ON LOWER(TRIM(y.code)) = LOWER(TRIM(p.`year_level`))
                OR LOWER(TRIM(y.label)) = LOWER(TRIM(p.`year_level`))
            SET p.`year_level_id` = y.id
            WHERE p.`year_level_id` IS NULL
                AND p.`year_level` IS NOT NULL
                AND TRIM(p.`year_level`) <> ''
                AND y.id IS NOT NULL"
        );
    }

    private function addForeignKey()
    {
        $tableName = 'applicant_application_preferences';
        $foreignName = 'applicant_application_preferences_year_level_id_foreign';

        if (!Schema::hasTable($tableName)
            || !Schema::hasTable('applicant_year_levels')
            || !Schema::hasColumn($tableName, 'year_level_id')
            || $this->foreignKeyExists($tableName, $foreignName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($foreignName) {
            $table->foreign('year_level_id', $foreignName)
                ->references('id')
                ->on('applicant_year_levels')
                ->onDelete('set null');
        });
    }

    private function dropLegacyYearLevelColumn()
    {
        if (!Schema::hasTable('applicant_application_preferences')
            || !Schema::hasColumn('applicant_application_preferences', 'year_level')) {
            return;
        }

        Schema::table('applicant_application_preferences', function (Blueprint $table) {
            $table->dropColumn('year_level');
        });
    }

    private function dropForeignKey()
    {
        $tableName = 'applicant_application_preferences';
        $foreignName = 'applicant_application_preferences_year_level_id_foreign';

        if (!Schema::hasTable($tableName) || !$this->foreignKeyExists($tableName, $foreignName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($foreignName) {
            $table->dropForeign($foreignName);
        });
    }

    private function addLegacyYearLevelColumn()
    {
        if (!Schema::hasTable('applicant_application_preferences')
            || Schema::hasColumn('applicant_application_preferences', 'year_level')) {
            return;
        }

        Schema::table('applicant_application_preferences', function (Blueprint $table) {
            if (Schema::hasColumn('applicant_application_preferences', 'year_level_id')) {
                $table->string('year_level', 255)->nullable()->after('year_level_id');
            } else {
                $table->string('year_level', 255)->nullable();
            }
        });
    }

    private function restoreLegacyYearLevelValues()
    {
        if (!Schema::hasTable('applicant_application_preferences')
            || !Schema::hasTable('applicant_year_levels')
            || !Schema::hasColumn('applicant_application_preferences', 'year_level')
            || !Schema::hasColumn('applicant_application_preferences', 'year_level_id')) {
            return;
        }

        DB::statement(
            "UPDATE `applicant_application_preferences` p
            JOIN `applicant_year_levels` y ON y.id = p.`year_level_id`
            SET p.`year_level` = COALESCE(NULLIF(TRIM(p.`year_level`), ''), COALESCE(NULLIF(y.code, ''), y.label))
            WHERE p.`year_level_id` IS NOT NULL"
        );
    }

    private function dropYearLevelIdColumn()
    {
        if (!Schema::hasTable('applicant_application_preferences')
            || !Schema::hasColumn('applicant_application_preferences', 'year_level_id')) {
            return;
        }

        if ($this->indexExists('applicant_application_preferences', 'applicant_application_preferences_year_level_id_idx')) {
            Schema::table('applicant_application_preferences', function (Blueprint $table) {
                $table->dropIndex('applicant_application_preferences_year_level_id_idx');
            });
        }

        Schema::table('applicant_application_preferences', function (Blueprint $table) {
            $table->dropColumn('year_level_id');
        });
    }

    private function dropLookupTable()
    {
        if (Schema::hasTable('applicant_year_levels')) {
            Schema::drop('applicant_year_levels');
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