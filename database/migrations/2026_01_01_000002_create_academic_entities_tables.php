<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAcademicEntitiesTables extends Migration
{
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('student_no')->unique();
            $table->string('name');
            $table->string('sex')->nullable();
            $table->tinyInteger('age')->nullable();
            $table->string('college')->nullable();
            $table->string('program')->nullable();
            $table->string('curriculum')->nullable();
            $table->string('year_level')->nullable();
            $table->string('scholarship')->nullable();
            $table->string('registration_no')->nullable();
            $table->string('school_year');
            $table->string('semester');
            $table->timestamps();
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code')->unique();
            $table->string('name');
            $table->string('program_type')->nullable();
            $table->unsignedBigInteger('department_id');
            $table->string('description')->nullable();
            $table->unsignedInteger('slots')->default(0);
            $table->string('track_category')->nullable();
            $table->boolean('non_filipino')->default(false);
            $table->unsignedBigInteger('dean_director_id')->nullable();
            $table->string('program_file')->nullable();
            
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->foreign('dean_director_id')->references('id')->on('faculties')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('applicants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('applicant_id')->unique();
            $table->string('lrn')->nullable();
            $table->string('last_name');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('suffix')->nullable();
            $table->string('nickname')->nullable();
            $table->string('gender')->default('Male');
            $table->string('nationality')->default('Filipino');
            $table->string('religion')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->integer('age')->nullable();
            $table->string('civil_status')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('email_address')->nullable();
            $table->string('photo')->nullable();
            
            $table->string('present_street')->nullable();
            $table->string('present_barangay')->nullable();
            $table->string('present_zipcode')->nullable();
            $table->string('present_municipality')->nullable();
            $table->string('present_province')->nullable();
            $table->string('present_region')->nullable();
            $table->boolean('same_as_present')->default(false);
            
            $table->string('permanent_street')->nullable();
            $table->string('permanent_barangay')->nullable();
            $table->string('permanent_zipcode')->nullable();
            $table->string('permanent_municipality')->nullable();
            $table->string('permanent_province')->nullable();
            $table->string('permanent_region')->nullable();
            
            $table->dateTime('exam_date')->nullable();
            $table->string('exam_room')->nullable();
            $table->string('exam_result_status')->default('Pending');
            $table->decimal('exam_score', 8, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code');
            $table->string('name');
            $table->decimal('units', 3, 1)->default(0);
            $table->tinyInteger('lec')->default(0);
            $table->tinyInteger('lab')->default(0);
            $table->string('days')->nullable();
            $table->string('time_start')->nullable();
            $table->string('time_end')->nullable();
            $table->string('room')->nullable();
            $table->unsignedBigInteger('faculty_id')->nullable();
            $table->string('year_section')->nullable();
            $table->string('course')->nullable();
            $table->string('semester')->nullable();
            $table->string('school_year')->nullable();
            $table->string('grading_status')->default('Open For Encoding');
            $table->string('load_type')->nullable();
            $table->decimal('credited_tuition_units', 5, 2)->nullable();
            $table->decimal('load_hours', 5, 2)->nullable();
            $table->string('added_by')->nullable();
            
            $table->foreign('faculty_id')->references('id')->on('faculties')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('applicants');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('students');
    }
}
