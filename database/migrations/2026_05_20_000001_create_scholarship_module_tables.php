<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScholarshipModuleTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('scholarship_programs')) {
            Schema::create('scholarship_programs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('category', 80);
                $table->string('coverage_type', 80);
                $table->decimal('coverage_value', 12, 2)->nullable();
                $table->string('school_year', 20)->nullable();
                $table->string('semester', 40)->nullable();
                $table->text('applicable_course_ids')->nullable();
                $table->string('year_level_eligibility')->nullable();
                $table->unsignedInteger('available_slots')->nullable();
                $table->decimal('maintaining_gwa', 5, 2)->nullable();
                $table->string('status', 30)->default('Open');
                $table->text('description')->nullable();
                $table->timestamps();

                $table->index(['category', 'status'], 'scholarship_program_category_status_idx');
                $table->index(['school_year', 'semester'], 'scholarship_program_term_idx');
            });
        }

        if (!Schema::hasTable('scholarship_student')) {
            Schema::create('scholarship_student', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('scholarship_program_id');
                $table->string('school_year', 20)->nullable();
                $table->string('semester', 40)->nullable();
                $table->string('application_status', 40)->default('Tagged');
                $table->string('evaluation_status', 40)->nullable();
                $table->string('approval_status', 40)->nullable();
                $table->string('award_status', 40)->default('Active');
                $table->string('renewal_status', 40)->nullable();
                $table->string('monitoring_status', 40)->nullable();
                $table->string('financial_posting_status', 40)->default('Pending');
                $table->decimal('posted_amount', 12, 2)->nullable();
                $table->decimal('discount_percent', 5, 2)->nullable();
                $table->decimal('current_gwa', 5, 2)->nullable();
                $table->date('application_date')->nullable();
                $table->date('approval_date')->nullable();
                $table->date('renewal_due_date')->nullable();
                $table->text('remarks')->nullable();
                $table->unsignedBigInteger('tagged_by_user_id')->nullable();
                $table->timestamps();

                $table->unique(['student_id', 'scholarship_program_id', 'school_year', 'semester'], 'scholarship_student_unique_term');
                $table->index(['award_status', 'financial_posting_status'], 'scholarship_student_status_idx');
                $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
                $table->foreign('scholarship_program_id')->references('id')->on('scholarship_programs')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('scholarship_student');
        Schema::dropIfExists('scholarship_programs');
    }
}
