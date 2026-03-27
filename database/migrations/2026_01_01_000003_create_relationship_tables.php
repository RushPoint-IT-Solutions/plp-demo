<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRelationshipTables extends Migration
{
    public function up()
    {
        Schema::create('student_subject', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('subject_id');
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            $table->unique(['student_id', 'subject_id']);
        });

        Schema::create('student_subject_grades', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('subject_id');
            $table->decimal('prelim', 3, 2)->nullable();
            $table->decimal('midterm', 3, 2)->nullable();
            $table->decimal('final', 3, 2)->nullable();
            $table->decimal('final_average', 3, 2)->nullable();
            $table->string('remarks')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            $table->unique(['subject_id', 'student_id']);
        });

        Schema::create('faculty_evaluations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('subject_id');
            $table->string('section')->nullable();
            $table->decimal('mean_score', 3, 1)->nullable();
            $table->timestamps();

            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
        });

        Schema::create('student_profiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id')->nullable();
            $table->string('student_no')->nullable()->unique();
            
            // Personal Information
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('suffix')->nullable();
            $table->string('nickname')->nullable();
            $table->string('gender')->nullable();
            $table->string('nationality')->nullable();
            $table->string('nationality_other')->nullable();
            $table->string('religion')->nullable();
            $table->string('religion_other')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->string('civil_status')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('student_email')->nullable();
            $table->string('profile_photo_path')->nullable();
            
            // Present Address
            $table->string('present_street')->nullable();
            $table->string('present_barangay')->nullable();
            $table->string('present_zipcode')->nullable();
            $table->string('present_municipality')->nullable();
            $table->string('present_province')->nullable();
            $table->string('present_region')->nullable();
            
            // Permanent Address
            $table->string('permanent_street')->nullable();
            $table->string('permanent_barangay')->nullable();
            $table->string('permanent_zipcode')->nullable();
            $table->string('permanent_municipality')->nullable();
            $table->string('permanent_province')->nullable();
            $table->string('permanent_region')->nullable();
            
            // Household Information Flags
            $table->boolean('same_as_present')->default(false);
            $table->boolean('is_orphan')->default(false);
            $table->boolean('is_first_gen')->default(false);
            $table->boolean('is_4ps')->default(false);
            $table->boolean('has_disability')->default(false);
            $table->boolean('is_foreign')->default(false);
            
            // Mother
            $table->string('mother_firstname')->nullable();
            $table->string('mother_middlename')->nullable();
            $table->string('mother_lastname')->nullable();
            $table->string('mother_contact')->nullable();
            $table->string('mother_occupation')->nullable();
            $table->boolean('mother_pensioner')->default(false);
            
            // Father
            $table->string('father_firstname')->nullable();
            $table->string('father_middlename')->nullable();
            $table->string('father_lastname')->nullable();
            $table->string('father_contact')->nullable();
            $table->string('father_occupation')->nullable();
            $table->boolean('father_pensioner')->default(false);
            
            // Guardian
            $table->string('guardian_firstname')->nullable();
            $table->string('guardian_middlename')->nullable();
            $table->string('guardian_lastname')->nullable();
            $table->string('guardian_contact')->nullable();
            $table->string('guardian_occupation')->nullable();
            $table->string('guardian_address')->nullable();
            
            // Family Income
            $table->string('parent_marital_status')->nullable();
            $table->string('monthly_family_income')->nullable();
            $table->integer('number_of_siblings')->nullable();
            $table->integer('household_members')->nullable();
            $table->integer('dependents')->nullable();
            
            // Education
            $table->string('junior_school')->nullable();
            $table->string('senior_school')->nullable();
            $table->string('shs_track_strand')->nullable();
            $table->boolean('no_k12')->default(false);
            $table->string('lrn')->nullable();
            
            // Socioeconomic
            $table->string('family_income_source')->nullable();
            $table->string('family_income_source_other')->nullable();
            $table->string('living_situation')->nullable();
            $table->string('living_situation_other')->nullable();
            $table->boolean('working_student')->default(false);
            $table->boolean('has_scholarship')->default(false);
            $table->boolean('first_in_family_college')->default(false);
            
            // Digital Access
            $table->text('internet_access')->nullable();
            $table->text('it_tools_access')->nullable();
            $table->text('devices')->nullable();
            $table->string('devices_other')->nullable();
            $table->text('lms_used')->nullable();
            $table->string('lms_used_other')->nullable();
            $table->text('lms_preferred')->nullable();
            $table->string('lms_preferred_other')->nullable();
            $table->text('lms_reasons')->nullable();
            $table->string('lms_reasons_other')->nullable();
            
            // Preferences
            $table->text('preferred_class_time')->nullable();
            $table->boolean('evening_classes')->default(false);
            
            $table->boolean('profile_complete')->default(false);
            
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_profiles');
        Schema::dropIfExists('faculty_evaluations');
        Schema::dropIfExists('student_subject_grades');
        Schema::dropIfExists('student_subject');
    }
}
