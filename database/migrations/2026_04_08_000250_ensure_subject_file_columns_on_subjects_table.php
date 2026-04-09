<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EnsureSubjectFileColumnsOnSubjectsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('subjects')) {
            return;
        }

        Schema::table('subjects', function (Blueprint $table) {
            if (!Schema::hasColumn('subjects', 'is_subject_file_record')) {
                $table->boolean('is_subject_file_record')->default(false)->after('name');
            }

            if (!Schema::hasColumn('subjects', 'is_core')) {
                $table->boolean('is_core')->default(false)->after('lab');
            }

            if (!Schema::hasColumn('subjects', 'is_applied')) {
                $table->boolean('is_applied')->default(false)->after('is_core');
            }

            if (!Schema::hasColumn('subjects', 'is_specialized')) {
                $table->boolean('is_specialized')->default(false)->after('is_applied');
            }
        });
    }

    public function down()
    {
        // Keep migration additive to avoid destructive schema changes.
    }
}
