<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddUniqueIndexToSubjectCodes extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('subjects')) {
            return;
        }

        if (!$this->indexExists('subjects', 'subjects_code_unique')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->unique('code', 'subjects_code_unique');
            });
        }
    }

    public function down()
    {
        if (!Schema::hasTable('subjects')) {
            return;
        }

        if ($this->indexExists('subjects', 'subjects_code_unique')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->dropUnique('subjects_code_unique');
            });
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        $rows = DB::select('SHOW INDEX FROM `' . $table . '` WHERE Key_name = ?', [$index]);

        return count($rows) > 0;
    }
}
