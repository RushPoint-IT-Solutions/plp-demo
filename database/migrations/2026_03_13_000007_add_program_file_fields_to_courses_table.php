<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProgramFileFieldsToCoursesTable extends Migration
{
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            if (!Schema::hasColumn('courses', 'program_type')) {
                $table->string('program_type')->nullable()->after('name');
            }
            if (!Schema::hasColumn('courses', 'department_id')) {
                $table->unsignedBigInteger('department_id')->nullable()->after('program_type');
                $table->index('department_id');
            }
            if (!Schema::hasColumn('courses', 'description')) {
                $table->string('description')->nullable()->after('department_id');
            }
            if (!Schema::hasColumn('courses', 'slots')) {
                $table->unsignedInteger('slots')->default(0)->after('description');
            }
            if (!Schema::hasColumn('courses', 'track_category')) {
                $table->string('track_category')->nullable()->after('slots');
            }
            if (!Schema::hasColumn('courses', 'non_filipino')) {
                $table->boolean('non_filipino')->default(false)->after('track_category');
            }
            if (!Schema::hasColumn('courses', 'dean_director_id')) {
                $table->unsignedBigInteger('dean_director_id')->nullable()->after('non_filipino');
                $table->index('dean_director_id');
            }
        });
    }

    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            if (Schema::hasColumn('courses', 'dean_director_id')) {
                $table->dropIndex(['dean_director_id']);
                $table->dropColumn('dean_director_id');
            }
            if (Schema::hasColumn('courses', 'non_filipino')) {
                $table->dropColumn('non_filipino');
            }
            if (Schema::hasColumn('courses', 'track_category')) {
                $table->dropColumn('track_category');
            }
            if (Schema::hasColumn('courses', 'slots')) {
                $table->dropColumn('slots');
            }
            if (Schema::hasColumn('courses', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('courses', 'department_id')) {
                $table->dropIndex(['department_id']);
                $table->dropColumn('department_id');
            }
            if (Schema::hasColumn('courses', 'program_type')) {
                $table->dropColumn('program_type');
            }
        });
    }
}
