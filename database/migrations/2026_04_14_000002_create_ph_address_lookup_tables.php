<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePhAddressLookupTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createRegionTable();
        $this->createProvinceTable();
        $this->createMunicipalityTable();
        $this->addAddressFkToApplicants();
        $this->addAddressFkToStudentProfiles();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropAddressFkFromStudentProfiles();
        $this->dropAddressFkFromApplicants();
        $this->dropMunicipalityTable();
        $this->dropProvinceTable();
        $this->dropRegionTable();
    }

    private function createRegionTable()
    {
        if (Schema::hasTable('ph_regions')) {
            return;
        }

        Schema::create('ph_regions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('psgc_code', 20)->nullable()->unique();
            $table->string('region_name', 255)->unique();
            $table->string('region_code', 20)->nullable();
            $table->timestamps();

            $table->index('region_code');
        });
    }

    private function createProvinceTable()
    {
        if (Schema::hasTable('ph_provinces')) {
            return;
        }

        Schema::create('ph_provinces', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('psgc_code', 20)->nullable()->unique();
            $table->string('province_name', 255)->unique();
            $table->unsignedBigInteger('region_id')->nullable();
            $table->timestamps();

            $table->index('region_id');

            $table->foreign('region_id', 'ph_provinces_region_id_foreign')
                ->references('id')
                ->on('ph_regions')
                ->onDelete('set null');
        });
    }

    private function createMunicipalityTable()
    {
        if (Schema::hasTable('ph_municipalities')) {
            return;
        }

        Schema::create('ph_municipalities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('psgc_code', 20)->nullable()->unique();
            $table->string('municipality_name', 255);
            $table->unsignedBigInteger('province_id')->nullable();
            $table->timestamps();

            $table->index('province_id');
            $table->unique(['province_id', 'municipality_name'], 'ph_municipalities_province_name_uniq');

            $table->foreign('province_id', 'ph_municipalities_province_id_foreign')
                ->references('id')
                ->on('ph_provinces')
                ->onDelete('set null');
        });
    }

    private function addAddressFkToApplicants()
    {
        if (!Schema::hasTable('applicants')) {
            return;
        }

        if (!Schema::hasColumn('applicants', 'present_region_id')) {
            Schema::table('applicants', function (Blueprint $table) {
                $table->unsignedBigInteger('present_region_id')->nullable()->after('present_region');
                $table->unsignedBigInteger('present_province_id')->nullable()->after('present_province');
                $table->unsignedBigInteger('present_municipality_id')->nullable()->after('present_municipality');

                $table->index('present_region_id', 'applicants_present_region_id_idx');
                $table->index('present_province_id', 'applicants_present_province_id_idx');
                $table->index('present_municipality_id', 'applicants_present_municipality_id_idx');
            });
        }

        if (!Schema::hasColumn('applicants', 'permanent_region_id')) {
            Schema::table('applicants', function (Blueprint $table) {
                $table->unsignedBigInteger('permanent_region_id')->nullable()->after('permanent_region');
                $table->unsignedBigInteger('permanent_province_id')->nullable()->after('permanent_province');
                $table->unsignedBigInteger('permanent_municipality_id')->nullable()->after('permanent_municipality');

                $table->index('permanent_region_id', 'applicants_permanent_region_id_idx');
                $table->index('permanent_province_id', 'applicants_permanent_province_id_idx');
                $table->index('permanent_municipality_id', 'applicants_permanent_municipality_id_idx');
            });
        }

        $this->backfillApplicantAddressIds('applicants');
        $this->addAddressForeignKeysToApplicants();
    }

    private function addAddressFkToStudentProfiles()
    {
        if (!Schema::hasTable('student_profiles')) {
            return;
        }

        if (!Schema::hasColumn('student_profiles', 'present_region_id')) {
            Schema::table('student_profiles', function (Blueprint $table) {
                $table->unsignedBigInteger('present_region_id')->nullable()->after('present_region');
                $table->unsignedBigInteger('present_province_id')->nullable()->after('present_province');
                $table->unsignedBigInteger('present_municipality_id')->nullable()->after('present_municipality');

                $table->index('present_region_id', 'student_profiles_present_region_id_idx');
                $table->index('present_province_id', 'student_profiles_present_province_id_idx');
                $table->index('present_municipality_id', 'student_profiles_present_municipality_id_idx');
            });
        }

        if (!Schema::hasColumn('student_profiles', 'permanent_region_id')) {
            Schema::table('student_profiles', function (Blueprint $table) {
                $table->unsignedBigInteger('permanent_region_id')->nullable()->after('permanent_region');
                $table->unsignedBigInteger('permanent_province_id')->nullable()->after('permanent_province');
                $table->unsignedBigInteger('permanent_municipality_id')->nullable()->after('permanent_municipality');

                $table->index('permanent_region_id', 'student_profiles_permanent_region_id_idx');
                $table->index('permanent_province_id', 'student_profiles_permanent_province_id_idx');
                $table->index('permanent_municipality_id', 'student_profiles_permanent_municipality_id_idx');
            });
        }

        $this->backfillApplicantAddressIds('student_profiles');
        $this->addAddressForeignKeysToStudentProfiles();
    }

    private function backfillApplicantAddressIds($tableName)
    {
        if (!Schema::hasTable($tableName)
            || !Schema::hasTable('ph_regions')
            || !Schema::hasTable('ph_provinces')
            || !Schema::hasTable('ph_municipalities')) {
            return;
        }

        $prefixes = ['present', 'permanent'];

        foreach ($prefixes as $prefix) {
            $regionColumn  = $prefix . '_region';
            $provinceColumn = $prefix . '_province';
            $cityColumn    = $prefix . '_municipality';
            $regionIdCol   = $prefix . '_region_id';
            $provinceIdCol = $prefix . '_province_id';
            $cityIdCol     = $prefix . '_municipality_id';

            if (!Schema::hasColumn($tableName, $regionColumn) || !Schema::hasColumn($tableName, $regionIdCol)) {
                continue;
            }

            DB::statement(
                "UPDATE `{$tableName}` t
                LEFT JOIN `ph_regions` r
                    ON LOWER(TRIM(r.`region_name`)) = LOWER(TRIM(t.`{$regionColumn}`))
                    OR LOWER(TRIM(r.`region_name`)) = LOWER(TRIM(t.`{$regionColumn}`))
                SET t.`{$regionIdCol}` = r.id
                WHERE t.`{$regionIdCol}` IS NULL
                    AND t.`{$regionColumn}` IS NOT NULL
                    AND TRIM(t.`{$regionColumn}`) <> ''",
                []
            );

            DB::statement(
                "UPDATE `{$tableName}` t
                LEFT JOIN `ph_regions` r
                    ON LOWER(TRIM(r.`region_name`)) = LOWER(TRIM(t.`{$regionColumn}`))
                LEFT JOIN `ph_provinces` p
                    ON p.`region_id` = r.id
                    AND LOWER(TRIM(p.`province_name`)) = LOWER(TRIM(t.`{$provinceColumn}`))
                SET t.`{$provinceIdCol}` = p.id
                WHERE t.`{$provinceIdCol}` IS NULL
                    AND t.`{$provinceColumn}` IS NOT NULL
                    AND TRIM(t.`{$provinceColumn}`) <> ''"
            );

            DB::statement(
                "UPDATE `{$tableName}` t
                LEFT JOIN `ph_regions` r
                    ON LOWER(TRIM(r.`region_name`)) = LOWER(TRIM(t.`{$regionColumn}`))
                LEFT JOIN `ph_provinces` p
                    ON p.`region_id` = r.id
                    AND LOWER(TRIM(p.`province_name`)) = LOWER(TRIM(t.`{$provinceColumn}`))
                LEFT JOIN `ph_municipalities` m
                    ON m.`province_id` = p.id
                    AND LOWER(TRIM(m.`municipality_name`)) = LOWER(TRIM(t.`{$cityColumn}`))
                SET t.`{$cityIdCol}` = m.id
                WHERE t.`{$cityIdCol}` IS NULL
                    AND t.`{$cityColumn}` IS NOT NULL
                    AND TRIM(t.`{$cityColumn}`) <> ''"
            );
        }
    }

    private function addAddressForeignKeysToApplicants()
    {
        $this->addForeignKey('applicants', 'present_region_id', 'ph_regions');
        $this->addForeignKey('applicants', 'present_province_id', 'ph_provinces');
        $this->addForeignKey('applicants', 'present_municipality_id', 'ph_municipalities');
        $this->addForeignKey('applicants', 'permanent_region_id', 'ph_regions');
        $this->addForeignKey('applicants', 'permanent_province_id', 'ph_provinces');
        $this->addForeignKey('applicants', 'permanent_municipality_id', 'ph_municipalities');
    }

    private function addAddressForeignKeysToStudentProfiles()
    {
        $this->addForeignKey('student_profiles', 'present_region_id', 'ph_regions');
        $this->addForeignKey('student_profiles', 'present_province_id', 'ph_provinces');
        $this->addForeignKey('student_profiles', 'present_municipality_id', 'ph_municipalities');
        $this->addForeignKey('student_profiles', 'permanent_region_id', 'ph_regions');
        $this->addForeignKey('student_profiles', 'permanent_province_id', 'ph_provinces');
        $this->addForeignKey('student_profiles', 'permanent_municipality_id', 'ph_municipalities');
    }

    private function addForeignKey($tableName, $columnName, $referenceTable)
    {
        if (!Schema::hasTable($tableName)
            || !Schema::hasTable($referenceTable)
            || !Schema::hasColumn($tableName, $columnName)) {
            return;
        }

        $foreignName = $tableName . '_' . $columnName . '_foreign';

        if ($this->foreignKeyExists($tableName, $foreignName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($columnName, $referenceTable, $foreignName) {
            $table->foreign($columnName, $foreignName)
                ->references('id')
                ->on($referenceTable)
                ->onDelete('set null');
        });
    }

    private function dropAddressFkFromApplicants()
    {
        $columns = [
            'present_region_id', 'present_province_id', 'present_municipality_id',
            'permanent_region_id', 'permanent_province_id', 'permanent_municipality_id',
        ];

        foreach ($columns as $col) {
            $this->dropForeignKeyAndColumn('applicants', $col);
        }
    }

    private function dropAddressFkFromStudentProfiles()
    {
        $columns = [
            'present_region_id', 'present_province_id', 'present_municipality_id',
            'permanent_region_id', 'permanent_province_id', 'permanent_municipality_id',
        ];

        foreach ($columns as $col) {
            $this->dropForeignKeyAndColumn('student_profiles', $col);
        }
    }

    private function dropForeignKeyAndColumn($tableName, $columnName)
    {
        if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, $columnName)) {
            return;
        }

        $foreignName = $tableName . '_' . $columnName . '_foreign';
        if ($this->foreignKeyExists($tableName, $foreignName)) {
            Schema::table($tableName, function (Blueprint $table) use ($foreignName) {
                $table->dropForeign($foreignName);
            });
        }

        $indexName = $tableName . '_' . $columnName . '_idx';
        if ($this->indexExists($tableName, $indexName)) {
            Schema::table($tableName, function (Blueprint $table) use ($indexName) {
                $table->dropIndex($indexName);
            });
        }

        Schema::table($tableName, function (Blueprint $table) use ($columnName) {
            $table->dropColumn($columnName);
        });
    }

    private function dropMunicipalityTable()
    {
        if (Schema::hasTable('ph_municipalities')) {
            Schema::dropIfExists('ph_municipalities');
        }
    }

    private function dropProvinceTable()
    {
        if (Schema::hasTable('ph_provinces')) {
            Schema::dropIfExists('ph_provinces');
        }
    }

    private function dropRegionTable()
    {
        if (Schema::hasTable('ph_regions')) {
            Schema::dropIfExists('ph_regions');
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
