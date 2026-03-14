<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateStudentSubjectGradesTable extends Migration
{
    public function up()
    {
        Schema::create('student_subject_grades', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('student_id');
            $table->decimal('prelim', 3, 2);
            $table->decimal('midterm', 3, 2);
            $table->decimal('final', 3, 2);
            $table->decimal('final_average', 3, 2);
            $table->string('remarks', 20);
            $table->timestamps();

            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->unique(['subject_id', 'student_id']);
        });

        // Seed minimal demo records for already-submitted faculty subjects.
        $submittedSubjectIds = DB::table('subjects')
            ->where('faculty', 'Abejo, M.')
            ->where('grading_status', 'Submitted')
            ->pluck('id');

        foreach ($submittedSubjectIds as $subjectId) {
            $studentIds = DB::table('student_subject')
                ->where('subject_id', $subjectId)
                ->pluck('student_id');

            foreach ($studentIds as $studentId) {
                $base = 1.25 + ((($studentId + $subjectId) % 8) * 0.25);
                $prelim = min(5.00, round($base, 2));
                $midterm = min(5.00, round($base + 0.15, 2));
                $final = min(5.00, round($base + 0.1, 2));
                $avg = round(($prelim + $midterm + $final) / 3, 2);

                DB::table('student_subject_grades')->updateOrInsert(
                    ['subject_id' => $subjectId, 'student_id' => $studentId],
                    [
                        'prelim' => $prelim,
                        'midterm' => $midterm,
                        'final' => $final,
                        'final_average' => $avg,
                        'remarks' => $avg <= 3.00 ? 'Passed' : 'Failed',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    public function down()
    {
        Schema::dropIfExists('student_subject_grades');
    }
}
