<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterStudentGradeFilesTable extends Migration
{
    public function up()
    {
        Schema::create('master_student_grade_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('student_no', 80)->unique();
            $table->string('student_name', 190);
            $table->string('course', 190);
            $table->string('year_level', 30);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('master_student_grade_files');
    }
}
