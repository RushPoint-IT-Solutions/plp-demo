<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBackgroundSchoolFieldsToStudentProfiles extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('student_profiles')) {
            return;
        }

        Schema::table('student_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('student_profiles', 'elementary_school')) {
                $table->string('elementary_school')->nullable();
            }

            if (!Schema::hasColumn('student_profiles', 'high_school')) {
                $table->string('high_school')->nullable();
            }

            if (!Schema::hasColumn('student_profiles', 'school_last_attended')) {
                $table->string('school_last_attended')->nullable();
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('student_profiles')) {
            return;
        }

        Schema::table('student_profiles', function (Blueprint $table) {
            foreach (['school_last_attended', 'high_school', 'elementary_school'] as $column) {
                if (Schema::hasColumn('student_profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
