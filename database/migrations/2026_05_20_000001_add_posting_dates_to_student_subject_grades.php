<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPostingDatesToStudentSubjectGrades extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('student_subject_grades')) {
            return;
        }

        Schema::table('student_subject_grades', function (Blueprint $table) {
            if (!Schema::hasColumn('student_subject_grades', 'midterm_posted_at')) {
                $table->timestamp('midterm_posted_at')->nullable()->after('draft_saved_at');
            }

            if (!Schema::hasColumn('student_subject_grades', 'final_posted_at')) {
                $table->timestamp('final_posted_at')->nullable()->after('midterm_posted_at');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('student_subject_grades')) {
            return;
        }

        Schema::table('student_subject_grades', function (Blueprint $table) {
            if (Schema::hasColumn('student_subject_grades', 'final_posted_at')) {
                $table->dropColumn('final_posted_at');
            }

            if (Schema::hasColumn('student_subject_grades', 'midterm_posted_at')) {
                $table->dropColumn('midterm_posted_at');
            }
        });
    }
}
