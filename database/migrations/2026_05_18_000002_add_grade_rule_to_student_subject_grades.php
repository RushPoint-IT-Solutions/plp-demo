<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGradeRuleToStudentSubjectGrades extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('student_subject_grades')) {
            return;
        }

        Schema::table('student_subject_grades', function (Blueprint $table) {
            if (!Schema::hasColumn('student_subject_grades', 'grade_rule_id')) {
                $table->unsignedBigInteger('grade_rule_id')->nullable()->after('final_average');
            }

            if (!Schema::hasColumn('student_subject_grades', 'draft_saved_at')) {
                $table->timestamp('draft_saved_at')->nullable()->after('remarks');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('student_subject_grades')) {
            return;
        }

        Schema::table('student_subject_grades', function (Blueprint $table) {
            if (Schema::hasColumn('student_subject_grades', 'draft_saved_at')) {
                $table->dropColumn('draft_saved_at');
            }

            if (Schema::hasColumn('student_subject_grades', 'grade_rule_id')) {
                $table->dropColumn('grade_rule_id');
            }
        });
    }
}
