<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApplicantsTable extends Migration
{
    public function up()
    {
        Schema::create('applicants', function (Blueprint $table) {
            $table->increments('id');
            $table->string('applicant_id')->unique();   // e.g. 2526B0177

            // Personal Information
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

            // Present Address
            $table->string('present_street')->nullable();
            $table->string('present_barangay')->nullable();
            $table->string('present_zipcode')->nullable();
            $table->string('present_municipality')->nullable();
            $table->string('present_province')->nullable();
            $table->string('present_region')->nullable();

            // Permanent Address
            $table->boolean('same_as_present')->default(false);
            $table->string('permanent_street')->nullable();
            $table->string('permanent_barangay')->nullable();
            $table->string('permanent_zipcode')->nullable();
            $table->string('permanent_municipality')->nullable();
            $table->string('permanent_province')->nullable();
            $table->string('permanent_region')->nullable();

            // Exam info
            $table->datetime('exam_date')->nullable();
            $table->string('exam_room')->nullable();
            $table->string('exam_result_status')->nullable(); // Passed / Failed / Pending
            $table->integer('exam_score')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('applicants');
    }
}
