<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WidenStudentSubjectGradeColumns extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('student_subject_grades')) {
            return;
        }

        DB::statement('ALTER TABLE student_subject_grades MODIFY prelim DECIMAL(5,2) NULL');
        DB::statement('ALTER TABLE student_subject_grades MODIFY midterm DECIMAL(5,2) NULL');
        DB::statement('ALTER TABLE student_subject_grades MODIFY `final` DECIMAL(5,2) NULL');
        DB::statement('ALTER TABLE student_subject_grades MODIFY final_average DECIMAL(5,2) NULL');
    }

    public function down()
    {
        if (!Schema::hasTable('student_subject_grades')) {
            return;
        }

        DB::statement('ALTER TABLE student_subject_grades MODIFY prelim DECIMAL(3,2) NULL');
        DB::statement('ALTER TABLE student_subject_grades MODIFY midterm DECIMAL(3,2) NULL');
        DB::statement('ALTER TABLE student_subject_grades MODIFY `final` DECIMAL(3,2) NULL');
        DB::statement('ALTER TABLE student_subject_grades MODIFY final_average DECIMAL(3,2) NULL');
    }
}
