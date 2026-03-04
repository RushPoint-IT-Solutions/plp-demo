<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentProfilesTable extends Migration
{
    public function up()
    {
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->bigIncrements('id');

            // ── Step 1: Personal Information ─────────────────────────────────
            $table->string('student_no', 50)->nullable();
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('middle_name', 100)->nullable();
            $table->string('suffix', 30)->nullable();
            $table->string('nickname', 80)->nullable();
            $table->string('gender', 10)->nullable();
            $table->string('nationality', 80)->nullable();
            $table->string('nationality_other', 100)->nullable();
            $table->string('religion', 80)->nullable();
            $table->string('religion_other', 100)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('place_of_birth', 150)->nullable();
            $table->string('civil_status', 40)->nullable();
            $table->string('mobile_number', 15)->nullable();
            $table->string('student_email', 150)->nullable();
            $table->string('profile_photo_path', 255)->nullable();

            // ── Step 1: Present Address ───────────────────────────────────────
            $table->string('present_street', 200)->nullable();
            $table->string('present_barangay', 150)->nullable();
            $table->string('present_zipcode', 10)->nullable();
            $table->string('present_municipality', 150)->nullable();
            $table->string('present_province', 100)->nullable();
            $table->string('present_region', 100)->nullable();

            // ── Step 1: Permanent Address ─────────────────────────────────────
            $table->string('permanent_street', 200)->nullable();
            $table->string('permanent_barangay', 150)->nullable();
            $table->string('permanent_zipcode', 10)->nullable();
            $table->string('permanent_municipality', 150)->nullable();
            $table->string('permanent_province', 100)->nullable();
            $table->string('permanent_region', 100)->nullable();

            // ── Step 1: Toggle questions ──────────────────────────────────────
            $table->boolean('same_as_present')->default(false);
            $table->boolean('is_orphan')->default(false);
            $table->boolean('is_first_gen')->default(false);
            $table->boolean('is_4ps')->default(false);
            $table->boolean('has_disability')->default(false);
            $table->boolean('is_foreign')->default(false);

            // ── Step 2: Mother ────────────────────────────────────────────────
            $table->string('mother_firstname', 100)->nullable();
            $table->string('mother_middlename', 100)->nullable();
            $table->string('mother_lastname', 100)->nullable();
            $table->string('mother_contact', 15)->nullable();
            $table->string('mother_occupation', 150)->nullable();
            $table->boolean('mother_pensioner')->default(false);

            // ── Step 2: Father ────────────────────────────────────────────────
            $table->string('father_firstname', 100)->nullable();
            $table->string('father_middlename', 100)->nullable();
            $table->string('father_lastname', 100)->nullable();
            $table->string('father_contact', 15)->nullable();
            $table->string('father_occupation', 150)->nullable();
            $table->boolean('father_pensioner')->default(false);

            // ── Step 2: Guardian ──────────────────────────────────────────────
            $table->string('guardian_firstname', 100)->nullable();
            $table->string('guardian_middlename', 100)->nullable();
            $table->string('guardian_lastname', 100)->nullable();
            $table->string('guardian_contact', 15)->nullable();
            $table->string('guardian_occupation', 150)->nullable();
            $table->text('guardian_address')->nullable();

            // ── Step 2: Household ─────────────────────────────────────────────
            $table->string('parent_marital_status', 60)->nullable();
            $table->string('monthly_family_income', 60)->nullable();
            $table->unsignedSmallInteger('number_of_siblings')->nullable();
            $table->unsignedSmallInteger('household_members')->nullable();
            $table->unsignedSmallInteger('dependents')->nullable();

            // ── Step 3: Education ─────────────────────────────────────────────
            $table->string('junior_school', 200)->nullable();
            $table->string('senior_school', 200)->nullable();
            $table->string('shs_track_strand', 150)->nullable();
            $table->boolean('no_k12')->default(false);
            $table->string('lrn', 20)->nullable();

            // ── Step 4: Other Information ─────────────────────────────────────
            $table->string('family_income_source', 60)->nullable();
            $table->string('family_income_source_other', 150)->nullable();
            $table->string('living_situation', 60)->nullable();
            $table->string('living_situation_other', 150)->nullable();
            $table->string('working_student', 10)->nullable();
            $table->string('has_scholarship', 10)->nullable();
            $table->string('first_in_family_college', 10)->nullable();
            $table->string('internet_access', 15)->nullable();
            $table->string('it_tools_access', 15)->nullable();
            $table->text('devices')->nullable();                  // JSON array
            $table->string('devices_other', 150)->nullable();
            $table->string('lms_used', 60)->nullable();
            $table->string('lms_used_other', 150)->nullable();
            $table->string('lms_preferred', 60)->nullable();
            $table->string('lms_preferred_other', 150)->nullable();
            $table->text('lms_reasons')->nullable();              // JSON array
            $table->string('lms_reasons_other', 150)->nullable();
            $table->string('preferred_class_time', 40)->nullable();
            $table->string('evening_classes', 10)->nullable();

            $table->boolean('profile_complete')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_profiles');
    }
}
