<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddSubjectFileIndexesToSubjectsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('subjects')) {
            return;
        }

        Schema::table('subjects', function (Blueprint $table) {
            if (!$this->indexExists('subjects', 'subjects_subject_file_code_idx')) {
                $table->index(['is_subject_file_record', 'code', 'id'], 'subjects_subject_file_code_idx');
            }

            if (!$this->indexExists('subjects', 'subjects_subject_file_name_idx')) {
                $table->index(['is_subject_file_record', 'name', 'id'], 'subjects_subject_file_name_idx');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('subjects')) {
            return;
        }

        Schema::table('subjects', function (Blueprint $table) {
            if ($this->indexExists('subjects', 'subjects_subject_file_code_idx')) {
                $table->dropIndex('subjects_subject_file_code_idx');
            }

            if ($this->indexExists('subjects', 'subjects_subject_file_name_idx')) {
                $table->dropIndex('subjects_subject_file_name_idx');
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
