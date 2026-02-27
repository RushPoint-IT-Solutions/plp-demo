<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentsTable extends Migration
{
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('student_no')->unique();
            $table->string('name');           // "Lastname, Firstname"
            $table->string('sex')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('college')->nullable();
            $table->string('program')->nullable();
            $table->string('curriculum')->nullable();
            $table->string('year_level')->nullable();
            $table->string('scholarship')->nullable();
            $table->string('registration_no')->nullable();
            $table->string('school_year')->nullable();   // "2025-2026"
            $table->string('semester')->nullable();      // "2nd Semester"
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('students');
    }
}
