<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EnsureProgramFileColumnsOnCoursesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('courses')) {
            return;
        }

        Schema::table('courses', function (Blueprint $table) {
            if (!Schema::hasColumn('courses', 'program_type')) {
                $table->string('program_type')->nullable();
            }

            if (!Schema::hasColumn('courses', 'department_id')) {
                $table->unsignedBigInteger('department_id')->nullable();
            }

            if (!Schema::hasColumn('courses', 'description')) {
                $table->string('description')->nullable();
            }

            if (!Schema::hasColumn('courses', 'slots')) {
                $table->unsignedInteger('slots')->default(0);
            }

            if (!Schema::hasColumn('courses', 'track_category')) {
                $table->string('track_category')->nullable();
            }

            if (!Schema::hasColumn('courses', 'non_filipino')) {
                $table->boolean('non_filipino')->default(false);
            }

            if (!Schema::hasColumn('courses', 'dean_director_id')) {
                $table->unsignedBigInteger('dean_director_id')->nullable();
            }

            if (!Schema::hasColumn('courses', 'program_file')) {
                $table->string('program_file')->nullable();
            }
        });

        if (Schema::hasColumn('courses', 'program_file')) {
            DB::table('courses')
                ->whereNull('program_file')
                ->orWhere('program_file', '')
                ->update(['program_file' => 'Pending Review']);
        }
    }

    public function down()
    {
        // Keep migration additive to avoid destructive schema changes.
    }
}
