<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddDateRangeToCourseCurricula extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('course_curricula')) {
            return;
        }

        Schema::table('course_curricula', function (Blueprint $table) {
            if (!Schema::hasColumn('course_curricula', 'date_from')) {
                $table->date('date_from')->nullable()->after('curriculum_year_code');
            }

            if (!Schema::hasColumn('course_curricula', 'date_to')) {
                $table->date('date_to')->nullable()->after('date_from');
            }
        });

        if (DB::getDriverName() === 'mysql' && !$this->indexExists('course_curricula', 'course_curricula_date_range_idx')) {
            Schema::table('course_curricula', function (Blueprint $table) {
                $table->index(['date_from', 'date_to'], 'course_curricula_date_range_idx');
            });
        }
    }

    public function down()
    {
        if (!Schema::hasTable('course_curricula')) {
            return;
        }

        if (DB::getDriverName() === 'mysql' && $this->indexExists('course_curricula', 'course_curricula_date_range_idx')) {
            Schema::table('course_curricula', function (Blueprint $table) {
                $table->dropIndex('course_curricula_date_range_idx');
            });
        }

        Schema::table('course_curricula', function (Blueprint $table) {
            if (Schema::hasColumn('course_curricula', 'date_to')) {
                $table->dropColumn('date_to');
            }

            if (Schema::hasColumn('course_curricula', 'date_from')) {
                $table->dropColumn('date_from');
            }
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
