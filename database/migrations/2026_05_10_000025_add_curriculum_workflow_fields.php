<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCurriculumWorkflowFields extends Migration
{
    public function up()
    {
        if (Schema::hasTable('courses')) {
            Schema::table('courses', function (Blueprint $table) {
                if (!Schema::hasColumn('courses', 'total_units')) {
                    $table->decimal('total_units', 7, 2)->nullable()->after('description');
                }

                if (!Schema::hasColumn('courses', 'academic_year')) {
                    $table->string('academic_year', 20)->nullable()->after('total_units');
                }
            });
        }

        if (Schema::hasTable('subjects')) {
            Schema::table('subjects', function (Blueprint $table) {
                if (!Schema::hasColumn('subjects', 'hours')) {
                    $table->decimal('hours', 6, 2)->nullable()->after('units');
                }

                if (!Schema::hasColumn('subjects', 'course_type')) {
                    $table->string('course_type', 40)->nullable()->after('hours');
                }
            });
        }

        if (Schema::hasTable('course_curricula')) {
            Schema::table('course_curricula', function (Blueprint $table) {
                if (!Schema::hasColumn('course_curricula', 'approval_status')) {
                    $table->string('approval_status', 40)->default('Draft')->after('title');
                }

                if (!Schema::hasColumn('course_curricula', 'department_head_approved_at')) {
                    $table->timestamp('department_head_approved_at')->nullable()->after('approval_status');
                }

                if (!Schema::hasColumn('course_curricula', 'dean_approved_at')) {
                    $table->timestamp('dean_approved_at')->nullable()->after('department_head_approved_at');
                }

                if (!Schema::hasColumn('course_curricula', 'registrar_approved_at')) {
                    $table->timestamp('registrar_approved_at')->nullable()->after('dean_approved_at');
                }

                if (!Schema::hasColumn('course_curricula', 'academic_council_approved_at')) {
                    $table->timestamp('academic_council_approved_at')->nullable()->after('registrar_approved_at');
                }

                if (!Schema::hasColumn('course_curricula', 'is_published')) {
                    $table->boolean('is_published')->default(false)->after('academic_council_approved_at');
                }

                if (!Schema::hasColumn('course_curricula', 'published_at')) {
                    $table->timestamp('published_at')->nullable()->after('is_published');
                }
            });
        }
    }

    public function down()
    {
        // Additive migration: keep columns to avoid data loss.
    }
}
