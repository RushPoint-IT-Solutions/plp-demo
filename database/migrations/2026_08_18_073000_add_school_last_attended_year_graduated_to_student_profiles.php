<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSchoolLastAttendedYearGraduatedToStudentProfiles extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('student_profiles')) {
            return;
        }

        Schema::table('student_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('student_profiles', 'school_last_attended_year_graduated')) {
                $table->string('school_last_attended_year_graduated')->nullable();
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('student_profiles')) {
            return;
        }

        Schema::table('student_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('student_profiles', 'school_last_attended_year_graduated')) {
                $table->dropColumn('school_last_attended_year_graduated');
            }
        });
    }
}
