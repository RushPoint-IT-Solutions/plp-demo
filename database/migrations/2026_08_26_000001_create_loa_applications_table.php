<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateLoaApplicationsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('loa_applications')) {
            $this->backfillCurrentLoaStudents();
            return;
        }

        Schema::create('loa_applications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('academic_term_id')->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->unsignedBigInteger('college_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->string('school_year', 20);
            $table->string('semester', 40);
            $table->string('filing_type', 30);
            $table->string('reason', 100);
            $table->text('reason_details')->nullable();
            $table->string('program', 255)->nullable();
            $table->string('college_department', 255)->nullable();
            $table->date('application_date');
            $table->string('status', 30)->default('Recorded');
            $table->unsignedBigInteger('recorded_by_user_id')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('academic_term_id')->references('id')->on('academic_terms')->onDelete('set null');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('set null');
            $table->foreign('college_id')->references('id')->on('colleges')->onDelete('set null');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            $table->foreign('recorded_by_user_id')->references('id')->on('users')->onDelete('set null');

            $table->index(['school_year', 'semester'], 'loa_term_index');
            $table->index('filing_type');
            $table->index('reason');
            $table->index('program');
            $table->index('college_department', 'loa_college_department_index');
        });

        $this->backfillCurrentLoaStudents();
    }

    public function down()
    {
        Schema::dropIfExists('loa_applications');
    }

    private function backfillCurrentLoaStudents(): void
    {
        if (!Schema::hasColumn('students', 'status')) {
            return;
        }

        $studentSchoolYear = Schema::hasColumn('students', 'school_year')
            ? 's.school_year'
            : DB::raw('NULL as school_year');
        $studentSemester = Schema::hasColumn('students', 'semester')
            ? 's.semester'
            : DB::raw('NULL as semester');
        $studentProgram = Schema::hasColumn('students', 'program')
            ? DB::raw('s.program as student_program')
            : DB::raw('NULL as student_program');
        $studentCollege = Schema::hasColumn('students', 'college')
            ? DB::raw('s.college as student_college')
            : DB::raw('NULL as student_college');

        $students = DB::table('students as s')
            ->leftJoin('courses as c', 'c.id', '=', 's.course_id')
            ->leftJoin('colleges as col', 'col.id', '=', 'c.college_id')
            ->leftJoin('departments as dep', 'dep.id', '=', 'c.department_id')
            ->leftJoin('academic_terms as at', 'at.id', '=', 's.academic_term_id')
            ->where('s.status', 'LOA')
            ->select([
                's.id as student_id',
                's.course_id',
                's.academic_term_id',
                $studentSchoolYear,
                $studentSemester,
                $studentProgram,
                $studentCollege,
                's.status_date',
                's.status_remarks',
                'c.code as course_code',
                'c.name as course_name',
                'c.college_id',
                'c.department_id',
                'col.name as college_name',
                'dep.description as department_name',
                'at.school_year as term_school_year',
                'at.term as term_semester',
            ])
            ->get();

        $now = now();
        foreach ($students as $student) {
            if (DB::table('loa_applications')->where('student_id', $student->student_id)->exists()) {
                continue;
            }

            $remarks = trim((string) $student->status_remarks);
            $normalizedRemarks = strtolower($remarks);
            $collegeDepartment = implode(' / ', array_values(array_unique(array_filter([
                trim((string) ($student->student_college ?: $student->college_name)),
                trim((string) $student->department_name),
            ]))));

            $filingType = 'enrolled';
            if (strpos($normalizedRemarks, 'late') !== false) {
                $filingType = 'late';
            } elseif (strpos($normalizedRemarks, 'non-enrolled') !== false || strpos($normalizedRemarks, 'non enrolled') !== false) {
                $filingType = 'non_enrolled';
            }

            DB::table('loa_applications')->insert([
                'student_id' => $student->student_id,
                'academic_term_id' => $student->academic_term_id,
                'course_id' => $student->course_id,
                'college_id' => $student->college_id,
                'department_id' => $student->department_id,
                'school_year' => trim((string) ($student->school_year ?: $student->term_school_year)) ?: 'Not Recorded',
                'semester' => trim((string) ($student->semester ?: $student->term_semester)) ?: 'Not Recorded',
                'filing_type' => $filingType,
                'reason' => $this->reasonFromRemarks($remarks),
                'reason_details' => $remarks ?: null,
                'program' => trim((string) ($student->student_program ?: $student->course_code ?: $student->course_name)) ?: null,
                'college_department' => $collegeDepartment ?: 'Not Recorded',
                'application_date' => $student->status_date ?: $now->toDateString(),
                'status' => 'Recorded',
                'recorded_by_user_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function reasonFromRemarks(string $remarks): string
    {
        $normalized = strtolower($remarks);
        $matches = [
            'medical' => 'Medical Condition',
            'financial' => 'Financial Constraint',
            'subject' => 'Unavailability of Subject',
            'work' => 'Work',
            'pregnan' => 'Pregnancy',
            'family' => 'Family Problem',
        ];

        foreach ($matches as $needle => $reason) {
            if (strpos($normalized, $needle) !== false) {
                return $reason;
            }
        }

        return 'Other';
    }
}
