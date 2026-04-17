<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HardenParentGradesModuleIndexes extends Migration
{
    public function up()
    {
        if (Schema::hasTable('student_grade_records')) {
            Schema::table('student_grade_records', function (Blueprint $table) {
                if (!$this->indexExists('student_grade_records', 'student_grade_records_student_id_idx')) {
                    $table->index('student_id', 'student_grade_records_student_id_idx');
                }

                if (!$this->indexExists('student_grade_records', 'sgr_student_term_idx')) {
                    $table->index(['student_id', 'school_year', 'term'], 'sgr_student_term_idx');
                }
            });

            if (Schema::hasTable('students')
                && Schema::hasColumn('student_grade_records', 'student_id')
                && !$this->foreignKeyExists('student_grade_records', 'sgr_student_fk')) {
                DB::statement(
                    'UPDATE student_grade_records sgr
                     LEFT JOIN students s ON s.id = sgr.student_id
                     SET sgr.student_id = NULL
                     WHERE sgr.student_id IS NOT NULL
                       AND s.id IS NULL'
                );

                Schema::table('student_grade_records', function (Blueprint $table) {
                    $table->foreign('student_id', 'sgr_student_fk')
                        ->references('id')
                        ->on('students')
                        ->onDelete('set null');
                });
            }
        }

        if (Schema::hasTable('parent_student_links')) {
            Schema::table('parent_student_links', function (Blueprint $table) {
                if (!$this->indexExists('parent_student_links', 'psl_parent_primary_idx')) {
                    $table->index(['parent_id', 'is_primary_contact'], 'psl_parent_primary_idx');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('student_grade_records')) {
            Schema::table('student_grade_records', function (Blueprint $table) {
                if ($this->foreignKeyExists('student_grade_records', 'sgr_student_fk')) {
                    $table->dropForeign('sgr_student_fk');
                }

                if ($this->indexExists('student_grade_records', 'sgr_student_term_idx')) {
                    $table->dropIndex('sgr_student_term_idx');
                }

                if ($this->indexExists('student_grade_records', 'student_grade_records_student_id_idx')) {
                    $table->dropIndex('student_grade_records_student_id_idx');
                }
            });
        }

        if (Schema::hasTable('parent_student_links')) {
            Schema::table('parent_student_links', function (Blueprint $table) {
                if ($this->indexExists('parent_student_links', 'psl_parent_primary_idx')) {
                    $table->dropIndex('psl_parent_primary_idx');
                }
            });
        }
    }

    private function foreignKeyExists($tableName, $constraintName)
    {
        $databaseName = DB::getDatabaseName();
        if (!$databaseName) {
            return false;
        }

        $result = DB::selectOne(
            'SELECT CONSTRAINT_NAME
             FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND CONSTRAINT_NAME = ?
               AND CONSTRAINT_TYPE = "FOREIGN KEY"
             LIMIT 1',
            [$databaseName, $tableName, $constraintName]
        );

        return !is_null($result);
    }

    private function indexExists($tableName, $indexName)
    {
        $databaseName = DB::getDatabaseName();
        if (!$databaseName) {
            return false;
        }

        $result = DB::selectOne(
            'SELECT INDEX_NAME
             FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND INDEX_NAME = ?
             LIMIT 1',
            [$databaseName, $tableName, $indexName]
        );

        return !is_null($result);
    }
}
