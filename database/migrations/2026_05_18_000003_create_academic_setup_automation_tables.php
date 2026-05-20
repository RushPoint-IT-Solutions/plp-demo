<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAcademicSetupAutomationTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('academic_setup_generation_logs')) {
            Schema::create('academic_setup_generation_logs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('generated_by_user_id')->nullable();
                $table->unsignedBigInteger('academic_term_id')->nullable();
                $table->unsignedBigInteger('course_id')->nullable();
                $table->unsignedBigInteger('year_block_id')->nullable();
                $table->string('school_year', 30);
                $table->string('semester', 40);
                $table->string('campus', 120)->nullable();
                $table->unsignedSmallInteger('max_students_per_section')->default(40);
                $table->unsignedInteger('programs_generated_count')->default(0);
                $table->unsignedInteger('courses_generated_count')->default(0);
                $table->unsignedInteger('sections_created_count')->default(0);
                $table->unsignedInteger('students_assigned_count')->default(0);
                $table->unsignedInteger('class_offerings_generated_count')->default(0);
                $table->unsignedInteger('rooms_assigned_count')->default(0);
                $table->unsignedInteger('faculty_assigned_count')->default(0);
                $table->unsignedInteger('schedules_generated_count')->default(0);
                $table->unsignedInteger('student_loads_generated_count')->default(0);
                $table->unsignedInteger('pending_issue_count')->default(0);
                $table->string('status', 30)->default('draft');
                $table->timestamp('published_at')->nullable();
                $table->timestamps();

                $table->foreign('generated_by_user_id', 'asgl_user_fk')
                    ->references('id')->on('users')->onDelete('set null');
                $table->foreign('academic_term_id', 'asgl_term_fk')
                    ->references('id')->on('academic_terms')->onDelete('set null');
                $table->foreign('course_id', 'asgl_course_fk')
                    ->references('id')->on('courses')->onDelete('set null');
                $table->foreign('year_block_id', 'asgl_year_fk')
                    ->references('id')->on('year_blocks')->onDelete('set null');

                $table->index(['academic_term_id', 'course_id', 'year_block_id'], 'asgl_scope_idx');
            });
        }

        if (!Schema::hasTable('academic_setup_pending_issues')) {
            Schema::create('academic_setup_pending_issues', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('generation_log_id');
                $table->string('issue_type', 80);
                $table->string('affected_type', 80)->nullable();
                $table->unsignedBigInteger('affected_id')->nullable();
                $table->string('affected_label', 190)->nullable();
                $table->text('description');
                $table->text('suggested_action')->nullable();
                $table->string('status', 30)->default('open');
                $table->timestamps();

                $table->foreign('generation_log_id', 'aspi_log_fk')
                    ->references('id')->on('academic_setup_generation_logs')->onDelete('cascade');
                $table->index(['generation_log_id', 'status'], 'aspi_log_status_idx');
                $table->index(['issue_type', 'status'], 'aspi_issue_status_idx');
            });
        }

        if (!Schema::hasTable('student_section_assignments')) {
            Schema::create('student_section_assignments', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('academic_term_id');
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('course_id')->nullable();
                $table->unsignedBigInteger('year_block_id')->nullable();
                $table->string('section', 80);
                $table->string('status', 30)->default('active');
                $table->string('approval_status', 40)->default('auto_approved');
                $table->text('flags')->nullable();
                $table->timestamps();

                $table->foreign('academic_term_id', 'ssa_term_fk')
                    ->references('id')->on('academic_terms')->onDelete('cascade');
                $table->foreign('student_id', 'ssa_student_fk')
                    ->references('id')->on('students')->onDelete('cascade');
                $table->foreign('course_id', 'ssa_course_fk')
                    ->references('id')->on('courses')->onDelete('set null');
                $table->foreign('year_block_id', 'ssa_year_fk')
                    ->references('id')->on('year_blocks')->onDelete('set null');

                $table->unique(['academic_term_id', 'student_id'], 'ssa_term_student_unique');
                $table->index(['academic_term_id', 'course_id', 'year_block_id', 'section'], 'ssa_section_scope_idx');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('student_section_assignments');
        Schema::dropIfExists('academic_setup_pending_issues');
        Schema::dropIfExists('academic_setup_generation_logs');
    }
}
