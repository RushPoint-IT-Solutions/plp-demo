<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAcademicTermLifecycle extends Migration
{
    public function up()
    {
        if (Schema::hasTable('academic_terms')) {
            Schema::table('academic_terms', function (Blueprint $table) {
                if (!Schema::hasColumn('academic_terms', 'status')) {
                    $table->string('status', 40)->default('Draft')->after('canonical_key');
                }
                if (!Schema::hasColumn('academic_terms', 'opened_at')) {
                    $table->timestamp('opened_at')->nullable()->after('status');
                }
                if (!Schema::hasColumn('academic_terms', 'closed_at')) {
                    $table->timestamp('closed_at')->nullable()->after('opened_at');
                }
                if (!Schema::hasColumn('academic_terms', 'archived_at')) {
                    $table->timestamp('archived_at')->nullable()->after('closed_at');
                }
                if (!Schema::hasColumn('academic_terms', 'opened_by_user_id')) {
                    $table->unsignedBigInteger('opened_by_user_id')->nullable()->after('archived_at');
                }
                if (!Schema::hasColumn('academic_terms', 'closed_by_user_id')) {
                    $table->unsignedBigInteger('closed_by_user_id')->nullable()->after('opened_by_user_id');
                }
            });
        }

        if (!Schema::hasTable('program_term_offerings')) {
            Schema::create('program_term_offerings', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('academic_term_id');
                $table->string('academic_year', 30);
                $table->string('semester', 40);
                $table->unsignedBigInteger('course_id')->nullable();
                $table->string('program_code', 40);
                $table->boolean('is_offered_this_term')->default(true);
                $table->boolean('accepting_new_students')->default(true);
                $table->string('allowed_year_levels', 80)->nullable();
                $table->string('curriculum_version', 80)->nullable();
                $table->string('status', 40)->default('Open');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->foreign('academic_term_id', 'pto_term_fk')
                    ->references('id')->on('academic_terms')->onDelete('cascade');
                $table->foreign('course_id', 'pto_course_fk')
                    ->references('id')->on('courses')->onDelete('set null');
                $table->foreign('created_by', 'pto_created_by_fk')
                    ->references('id')->on('users')->onDelete('set null');

                $table->unique(['academic_term_id', 'course_id'], 'pto_term_course_unique');
                $table->index(['academic_year', 'semester', 'status'], 'pto_term_status_idx');
            });
        }

        if (!Schema::hasTable('student_promotions')) {
            Schema::create('student_promotions', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('from_academic_term_id')->nullable();
                $table->unsignedBigInteger('to_academic_term_id')->nullable();
                $table->unsignedBigInteger('from_year_block_id')->nullable();
                $table->unsignedBigInteger('to_year_block_id')->nullable();
                $table->string('promotion_status', 60)->default('Promoted');
                $table->text('remarks')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->foreign('student_id', 'sp_student_fk')
                    ->references('id')->on('students')->onDelete('cascade');
                $table->foreign('from_academic_term_id', 'sp_from_term_fk')
                    ->references('id')->on('academic_terms')->onDelete('set null');
                $table->foreign('to_academic_term_id', 'sp_to_term_fk')
                    ->references('id')->on('academic_terms')->onDelete('set null');
                $table->foreign('from_year_block_id', 'sp_from_year_fk')
                    ->references('id')->on('year_blocks')->onDelete('set null');
                $table->foreign('to_year_block_id', 'sp_to_year_fk')
                    ->references('id')->on('year_blocks')->onDelete('set null');
                $table->foreign('created_by', 'sp_created_by_fk')
                    ->references('id')->on('users')->onDelete('set null');

                $table->unique(['student_id', 'to_academic_term_id'], 'sp_student_to_term_unique');
                $table->index(['from_academic_term_id', 'promotion_status'], 'sp_from_status_idx');
            });
        }

        if (!Schema::hasTable('academic_term_lifecycle_logs')) {
            Schema::create('academic_term_lifecycle_logs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('from_academic_term_id')->nullable();
                $table->unsignedBigInteger('to_academic_term_id')->nullable();
                $table->unsignedBigInteger('created_by_user_id')->nullable();
                $table->string('action', 60);
                $table->string('status', 40)->default('draft');
                $table->unsignedInteger('programs_opened_count')->default(0);
                $table->unsignedInteger('programs_not_opened_count')->default(0);
                $table->unsignedInteger('students_promoted_count')->default(0);
                $table->unsignedInteger('irregular_students_count')->default(0);
                $table->unsignedInteger('sections_created_count')->default(0);
                $table->unsignedInteger('class_offerings_generated_count')->default(0);
                $table->unsignedInteger('rooms_assigned_count')->default(0);
                $table->unsignedInteger('faculty_assigned_count')->default(0);
                $table->unsignedInteger('schedules_generated_count')->default(0);
                $table->unsignedInteger('student_loads_generated_count')->default(0);
                $table->unsignedInteger('pending_issue_count')->default(0);
                $table->longText('report_payload')->nullable();
                $table->timestamp('published_at')->nullable();
                $table->timestamps();

                $table->foreign('from_academic_term_id', 'atll_from_term_fk')
                    ->references('id')->on('academic_terms')->onDelete('set null');
                $table->foreign('to_academic_term_id', 'atll_to_term_fk')
                    ->references('id')->on('academic_terms')->onDelete('set null');
                $table->foreign('created_by_user_id', 'atll_user_fk')
                    ->references('id')->on('users')->onDelete('set null');

                $table->index(['from_academic_term_id', 'to_academic_term_id'], 'atll_terms_idx');
                $table->index(['action', 'status'], 'atll_action_status_idx');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('academic_term_lifecycle_logs');
        Schema::dropIfExists('student_promotions');
        Schema::dropIfExists('program_term_offerings');

        if (Schema::hasTable('academic_terms')) {
            Schema::table('academic_terms', function (Blueprint $table) {
                foreach (['closed_by_user_id', 'opened_by_user_id', 'archived_at', 'closed_at', 'opened_at', 'status'] as $column) {
                    if (Schema::hasColumn('academic_terms', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
}
