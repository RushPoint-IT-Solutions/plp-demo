<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFacultyFieldsToSubjectsTable extends Migration
{
    public function up()
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->string('year_section')->nullable()->after('room');
            $table->string('course')->nullable()->after('year_section');
            $table->string('grading_status')->default('Open For Encoding')->after('school_year');
        });
    }

    public function down()
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn(['year_section', 'course', 'grading_status']);
        });
    }
}
