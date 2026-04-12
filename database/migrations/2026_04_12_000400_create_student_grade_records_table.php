<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentGradeRecordsTable extends Migration
{
    public function up()
    {
        Schema::create('student_grade_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id')->nullable();
            $table->string('student_no', 80);
            $table->string('school_year', 30);
            $table->string('term', 30);
            $table->string('subject_code', 60);
            $table->string('equiv_subject_code', 60)->nullable();
            $table->string('professor', 190)->nullable();
            $table->string('description', 255)->nullable();
            $table->decimal('units', 5, 1)->default(0);
            $table->string('status', 30)->nullable();
            $table->string('section_code', 60)->nullable();
            $table->decimal('final_grade', 5, 2)->nullable();
            $table->boolean('inc')->default(false);
            $table->string('grade_status', 10)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->timestamps();

            $table->index('student_no');
            $table->index(['student_no', 'school_year', 'term']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_grade_records');
    }
}
