<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddYearGraduatedFieldsToStudentProfiles extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('student_profiles')) {
            return;
        }

        Schema::table('student_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('student_profiles', 'elementary_year_graduated')) {
                $table->string('elementary_year_graduated')->nullable();
            }

            if (!Schema::hasColumn('student_profiles', 'high_school_year_graduated')) {
                $table->string('high_school_year_graduated')->nullable();
            }

            if (!Schema::hasColumn('student_profiles', 'junior_school_year_graduated')) {
                $table->string('junior_school_year_graduated')->nullable();
            }

            if (!Schema::hasColumn('student_profiles', 'senior_school_year_graduated')) {
                $table->string('senior_school_year_graduated')->nullable();
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('student_profiles')) {
            return;
        }

        Schema::table('student_profiles', function (Blueprint $table) {
            foreach (['senior_school_year_graduated', 'junior_school_year_graduated', 'high_school_year_graduated', 'elementary_year_graduated'] as $column) {
                if (Schema::hasColumn('student_profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
