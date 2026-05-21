<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ScopeSubjectCodeUniqueToSubjectFile extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('subjects')) {
            return;
        }

        if ($this->indexExists('subjects', 'subjects_code_unique')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->dropUnique('subjects_code_unique');
            });
        }

        if (!Schema::hasColumn('subjects', 'subject_file_unique_code')) {
            DB::statement("
                ALTER TABLE `subjects`
                ADD `subject_file_unique_code` VARCHAR(255)
                GENERATED ALWAYS AS (
                    CASE
                        WHEN COALESCE(`is_subject_file_record`, 0) = 1 THEN `code`
                        ELSE NULL
                    END
                ) STORED
            ");
        }

        if (!$this->indexExists('subjects', 'subjects_subject_file_code_unique')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->unique('subject_file_unique_code', 'subjects_subject_file_code_unique');
            });
        }
    }

    public function down()
    {
        if (!Schema::hasTable('subjects')) {
            return;
        }

        if ($this->indexExists('subjects', 'subjects_subject_file_code_unique')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->dropUnique('subjects_subject_file_code_unique');
            });
        }

        if (Schema::hasColumn('subjects', 'subject_file_unique_code')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->dropColumn('subject_file_unique_code');
            });
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        $rows = DB::select('SHOW INDEX FROM `' . $table . '` WHERE Key_name = ?', [$index]);

        return count($rows) > 0;
    }
}
