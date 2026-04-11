<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Applicant;
use App\Faculty;
use App\MasterFacultyFile;
use App\MasterStudentProfileFile;
use App\MasterStudentGradeFile;
use App\Student;

class C3TrashDataSeeder extends Seeder
{
    public function run()
    {
        $this->seedFacultyEntities(30);
        $this->seedApplicantEntities(30);
        $this->seedStudentEntities(30);

        $this->seedFacultyRows(30);
        $this->seedStudentProfileRows(30);
        $this->seedStudentGradeRows(30);

        $this->command->info('C3TrashDataSeeder: ensured 30 records each for faculty, applicant, student, and C3 master-file tables.');
    }

    private function seedFacultyEntities(int $target): void
    {
        for ($i = 1; $i <= $target; $i++) {
            Faculty::updateOrCreate([
                'code' => 'TRFAC-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
            ], [
                'name' => 'Trash Faculty Entity ' . $i,
            ]);
        }
    }

    private function seedApplicantEntities(int $target): void
    {
        // --- Schema-awareness for applicants ---
        $hasExamResultStatusColumn   = Schema::hasColumn('applicants', 'exam_result_status');
        $hasExamResultStatusIdColumn = Schema::hasColumn('applicants', 'exam_result_status_id');

        // Ensure the lookup row exists and grab its id when needed
        $examResultStatusId = null;
        if ($hasExamResultStatusIdColumn && Schema::hasTable('applicant_exam_result_statuses')) {
            DB::table('applicant_exam_result_statuses')->updateOrInsert(
                ['code' => 'Pending'],
                ['label' => 'Pending', 'created_at' => now(), 'updated_at' => now()]
            );
            $examResultStatusId = DB::table('applicant_exam_result_statuses')
                ->where('code', 'Pending')
                ->value('id');
        }

        for ($i = 1; $i <= $target; $i++) {
            $basePayload = [
                'first_name'    => 'TrashApplicant' . $i,
                'last_name'     => 'Seed',
                'middle_name'   => null,
                'gender'        => $i % 2 === 0 ? 'Male' : 'Female',
                'nationality'   => 'Filipino',
                'religion'      => 'Roman Catholic',
                'civil_status'  => 'Single',
                'mobile_number' => '0917' . str_pad((string) $i, 7, '0', STR_PAD_LEFT),
                'email_address' => 'trash.applicant' . $i . '@example.test',
            ];

            if ($hasExamResultStatusColumn) {
                $basePayload['exam_result_status'] = 'Pending';
            }
            if ($hasExamResultStatusIdColumn) {
                $basePayload['exam_result_status_id'] = $examResultStatusId;
            }

            Applicant::updateOrCreate([
                'applicant_id' => 'TRAPP26' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
            ], $basePayload);
        }
    }

    private function seedStudentEntities(int $target): void
    {
        // --- Schema-awareness for students ---
        $hasProgramColumn       = Schema::hasColumn('students', 'program');
        $hasCourseIdColumn      = Schema::hasColumn('students', 'course_id');
        $hasYearLevelColumn     = Schema::hasColumn('students', 'year_level');
        $hasYearBlockIdColumn   = Schema::hasColumn('students', 'year_block_id');
        $hasSchoolYearColumn    = Schema::hasColumn('students', 'school_year');
        $hasSemesterColumn      = Schema::hasColumn('students', 'semester');
        $hasAcademicTermIdColumn = Schema::hasColumn('students', 'academic_term_id');

        $programs = [
            'Bachelor of Science in Computer Science',
            'Bachelor of Science in Information Technology',
            'Bachelor of Science in Information Systems',
            'Bachelor of Science in Computer Engineering',
        ];
        $years = ['First', 'Second', 'Third', 'Fourth'];
        $semesters = ['First', 'Second'];

        // Resolve FK dimension lookups once
        $courseIds = [];
        if ($hasCourseIdColumn && Schema::hasTable('courses')) {
            $courseIds = DB::table('courses')->orderBy('id')->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        }

        $yearBlockIds = [];
        if ($hasYearBlockIdColumn && Schema::hasTable('year_blocks')) {
            $yearBlockIds = DB::table('year_blocks')->orderBy('id')->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        }

        $academicTermIds = [];
        if ($hasAcademicTermIdColumn && Schema::hasTable('academic_terms')) {
            $academicTermIds = DB::table('academic_terms')->orderBy('id')->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        }

        for ($i = 1; $i <= $target; $i++) {
            $basePayload = [
                'name'            => 'Trash Student Entity ' . $i,
                'sex'             => $i % 2 === 0 ? 'Male' : 'Female',
                'age'             => 18 + ($i % 6),
                'college'         => 'College of Computer Studies',
                'curriculum'      => '2025',
                'scholarship'     => null,
                'registration_no' => 'TRREG' . str_pad((string) $i, 5, '0', STR_PAD_LEFT),
            ];

            if ($hasProgramColumn) {
                $basePayload['program'] = $programs[$i % count($programs)];
            }
            if ($hasCourseIdColumn && count($courseIds)) {
                $basePayload['course_id'] = $courseIds[$i % count($courseIds)];
            }
            if ($hasYearLevelColumn) {
                $basePayload['year_level'] = $years[$i % count($years)];
            }
            if ($hasYearBlockIdColumn && count($yearBlockIds)) {
                $basePayload['year_block_id'] = $yearBlockIds[$i % count($yearBlockIds)];
            }
            if ($hasSchoolYearColumn) {
                $basePayload['school_year'] = '2025-2026';
            }
            if ($hasSemesterColumn) {
                $basePayload['semester'] = $semesters[$i % count($semesters)];
            }
            if ($hasAcademicTermIdColumn && count($academicTermIds)) {
                $basePayload['academic_term_id'] = $academicTermIds[$i % count($academicTermIds)];
            }

            Student::updateOrCreate([
                'student_no' => 'TRSTD26' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
            ], $basePayload);
        }
    }

    private function seedFacultyRows(int $target): void
    {
        $current = MasterFacultyFile::query()->count();
        if ($current >= $target) {
            return;
        }

        $departments = ['Computer Studies', 'Engineering', 'Business Administration', 'Education'];

        for ($i = $current + 1; $i <= $target; $i++) {
            MasterFacultyFile::create([
                'code'           => 'TRF-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'name'           => 'Trash Faculty ' . $i,
                'department'     => $departments[$i % count($departments)],
                'status'         => $i % 5 === 0 ? 'Inactive' : 'Active',
                'config_payload' => null,
            ]);
        }
    }

    private function seedStudentProfileRows(int $target): void
    {
        $current = MasterStudentProfileFile::query()->count();
        if ($current >= $target) {
            return;
        }

        $courses = [
            'Bachelor of Science in Computer Science',
            'Bachelor of Science in Information Technology',
            'Bachelor of Science in Information Systems',
            'Bachelor of Science in Computer Engineering',
        ];
        $years = ['First', 'Second', 'Third', 'Fourth'];

        for ($i = $current + 1; $i <= $target; $i++) {
            MasterStudentProfileFile::create([
                'student_no'   => 'TRP26' . str_pad((string) $i, 5, '0', STR_PAD_LEFT),
                'student_name' => 'Trash Profile Student ' . $i,
                'course'       => $courses[$i % count($courses)],
                'year_level'   => $years[$i % count($years)],
            ]);
        }
    }

    private function seedStudentGradeRows(int $target): void
    {
        $current = MasterStudentGradeFile::query()->count();
        if ($current >= $target) {
            return;
        }

        $courses = [
            'Bachelor of Science in Computer Science',
            'Bachelor of Science in Information Technology',
            'Bachelor of Science in Information Systems',
            'Bachelor of Science in Computer Engineering',
        ];
        $years = ['First', 'Second', 'Third', 'Fourth'];

        for ($i = $current + 1; $i <= $target; $i++) {
            MasterStudentGradeFile::create([
                'student_no'   => 'TRG26' . str_pad((string) $i, 5, '0', STR_PAD_LEFT),
                'student_name' => 'Trash Grade Student ' . $i,
                'course'       => $courses[$i % count($courses)],
                'year_level'   => $years[$i % count($years)],
            ]);
        }
    }
}
