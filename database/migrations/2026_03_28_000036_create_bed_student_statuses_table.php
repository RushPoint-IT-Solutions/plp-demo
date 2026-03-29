<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBedStudentStatusesTable extends Migration
{
    public function up()
    {
        Schema::create('bed_student_statuses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('student_no', 80);
            $table->string('student_name', 190);
            $table->string('course', 190)->nullable();
            $table->string('year_level', 30)->nullable();
            $table->string('section', 20)->nullable();
            $table->string('school_year', 30)->nullable();
            $table->string('term', 30)->nullable();
            $table->boolean('no_payment')->default(false);
            $table->boolean('no_section')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bed_student_statuses');
    }
}
