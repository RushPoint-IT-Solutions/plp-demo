<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WaveKStrictifyMasterFacultyStatus extends Migration
{
    public function up()
    {
        $this->createLookupTable();
        $this->seedLookupValues();
        $this->addStatusIdColumn();
        $this->backfillStatusIds();
        $this->addForeignKey();
        $this->dropLegacyStatusColumn();
    }

    public function down()
    {
        $this->dropForeignKey();
        $this->addLegacyStatusColumn();
        $this->restoreLegacyStatusValues();
        $this->dropStatusIdColumn();
        $this->dropLookupTable();
    }

    private function createLookupTable()
    {
        if (Schema::hasTable('master_faculty_statuses')) {
            return;
        }

        Schema::create('master_faculty_statuses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code', 60)->unique();
            $table->string('label', 120)->nullable();
            $table->timestamps();
        });
    }

    private function seedLookupValues()
    {
        if (!Schema::hasTable('master_faculty_statuses')) {
            return;
        }

        $now = now();
        foreach (['Active', 'Inactive'] as $statusLabel) {
            DB::table('master_faculty_statuses')->updateOrInsert(
                ['code' => $statusLabel],
                ['label' => $statusLabel, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        if (!Schema::hasTable('master_faculty_files') || !Schema::hasColumn('master_faculty_files', 'status')) {
            return;
        }

        $statusValues = DB::table('master_faculty_files')
            ->select('status')
            ->whereNotNull('status')
            ->where('status', '<>', '')
            ->distinct()
            ->pluck('status');

        foreach ($statusValues as $value) {
            $normalized = trim((string) $value);
            if ($normalized === '') {
                continue;
            }

            DB::table('master_faculty_statuses')->updateOrInsert(
                ['code' => $normalized],
                ['label' => $normalized, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    private function addStatusIdColumn()
    {
        if (!Schema::hasTable('master_faculty_files')
            || !Schema::hasTable('master_faculty_statuses')
            || Schema::hasColumn('master_faculty_files', 'status_id')) {
            return;
        }

        Schema::table('master_faculty_files', function (Blueprint $table) {
            if (Schema::hasColumn('master_faculty_files', 'status')) {
                $table->unsignedBigInteger('status_id')->nullable()->after('status');
            } else {
                $table->unsignedBigInteger('status_id')->nullable()->after('department');
            }

            $table->index('status_id', 'master_faculty_files_status_id_idx');
        });
    }

    private function backfillStatusIds()
    {
        if (!Schema::hasTable('master_faculty_files')
            || !Schema::hasTable('master_faculty_statuses')
            || !Schema::hasColumn('master_faculty_files', 'status')
            || !Schema::hasColumn('master_faculty_files', 'status_id')) {
            return;
        }

        DB::statement(
            "UPDATE `master_faculty_files` f
            LEFT JOIN `master_faculty_statuses` s
                ON LOWER(TRIM(s.code)) = LOWER(TRIM(f.`status`))
                OR LOWER(TRIM(s.label)) = LOWER(TRIM(f.`status`))
            SET f.`status_id` = s.id
            WHERE f.`status_id` IS NULL
                AND f.`status` IS NOT NULL
                AND TRIM(f.`status`) <> ''
                AND s.id IS NOT NULL"
        );
    }

    private function addForeignKey()
    {
        $tableName = 'master_faculty_files';
        $foreignName = 'master_faculty_files_status_id_foreign';

        if (!Schema::hasTable($tableName)
            || !Schema::hasTable('master_faculty_statuses')
            || !Schema::hasColumn($tableName, 'status_id')
            || $this->foreignKeyExists($tableName, $foreignName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($foreignName) {
            $table->foreign('status_id', $foreignName)
                ->references('id')
                ->on('master_faculty_statuses')
                ->onDelete('set null');
        });
    }

    private function dropLegacyStatusColumn()
    {
        if (!Schema::hasTable('master_faculty_files')
            || !Schema::hasColumn('master_faculty_files', 'status')) {
            return;
        }

        Schema::table('master_faculty_files', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }

    private function dropForeignKey()
    {
        $tableName = 'master_faculty_files';
        $foreignName = 'master_faculty_files_status_id_foreign';

        if (!Schema::hasTable($tableName) || !$this->foreignKeyExists($tableName, $foreignName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($foreignName) {
            $table->dropForeign($foreignName);
        });
    }

    private function addLegacyStatusColumn()
    {
        if (!Schema::hasTable('master_faculty_files')
            || Schema::hasColumn('master_faculty_files', 'status')) {
            return;
        }

        Schema::table('master_faculty_files', function (Blueprint $table) {
            if (Schema::hasColumn('master_faculty_files', 'status_id')) {
                $table->string('status', 30)->nullable()->after('status_id');
            } else {
                $table->string('status', 30)->nullable()->after('department');
            }
        });
    }

    private function restoreLegacyStatusValues()
    {
        if (!Schema::hasTable('master_faculty_files')
            || !Schema::hasTable('master_faculty_statuses')
            || !Schema::hasColumn('master_faculty_files', 'status')
            || !Schema::hasColumn('master_faculty_files', 'status_id')) {
            return;
        }

        DB::statement(
            "UPDATE `master_faculty_files` f
            JOIN `master_faculty_statuses` s ON s.id = f.`status_id`
            SET f.`status` = COALESCE(NULLIF(TRIM(f.`status`), ''), COALESCE(NULLIF(s.code, ''), s.label))
            WHERE f.`status_id` IS NOT NULL"
        );
    }

    private function dropStatusIdColumn()
    {
        if (!Schema::hasTable('master_faculty_files')
            || !Schema::hasColumn('master_faculty_files', 'status_id')) {
            return;
        }

        if ($this->indexExists('master_faculty_files', 'master_faculty_files_status_id_idx')) {
            Schema::table('master_faculty_files', function (Blueprint $table) {
                $table->dropIndex('master_faculty_files_status_id_idx');
            });
        }

        Schema::table('master_faculty_files', function (Blueprint $table) {
            $table->dropColumn('status_id');
        });
    }

    private function dropLookupTable()
    {
        if (Schema::hasTable('master_faculty_statuses')) {
            Schema::drop('master_faculty_statuses');
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