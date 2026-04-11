<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddPrerequisitesPerformanceIndexes extends Migration
{
    public function up()
    {
        if (Schema::hasTable('course_curriculum_subjects') && !$this->indexExists('course_curriculum_subjects', 'cc_subjects_curriculum_display_idx')) {
            Schema::table('course_curriculum_subjects', function (Blueprint $table) {
                $table->index(
                    ['course_curriculum_id', 'display_order', 'id'],
                    'cc_subjects_curriculum_display_idx'
                );
            });
        }

        if (Schema::hasTable('curriculum_subject_requisites') && !$this->indexExists('curriculum_subject_requisites', 'csr_curriculum_subject_sort_idx')) {
            Schema::table('curriculum_subject_requisites', function (Blueprint $table) {
                $table->index(
                    ['course_curriculum_subject_id', 'sort_order'],
                    'csr_curriculum_subject_sort_idx'
                );
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('course_curriculum_subjects') && $this->indexExists('course_curriculum_subjects', 'cc_subjects_curriculum_display_idx')) {
            Schema::table('course_curriculum_subjects', function (Blueprint $table) {
                $table->dropIndex('cc_subjects_curriculum_display_idx');
            });
        }

        if (Schema::hasTable('curriculum_subject_requisites') && $this->indexExists('curriculum_subject_requisites', 'csr_curriculum_subject_sort_idx')) {
            Schema::table('curriculum_subject_requisites', function (Blueprint $table) {
                $table->dropIndex('csr_curriculum_subject_sort_idx');
            });
        }
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
