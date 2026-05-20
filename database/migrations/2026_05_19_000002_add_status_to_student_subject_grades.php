<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToStudentSubjectGrades extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('student_subject_grades')) {
            return;
        }

        Schema::table('student_subject_grades', function (Blueprint $table) {
            if (!Schema::hasColumn('student_subject_grades', 'status')) {
                $table->string('status', 30)->nullable()->after('final_average');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('student_subject_grades') || !Schema::hasColumn('student_subject_grades', 'status')) {
            return;
        }

        Schema::table('student_subject_grades', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
