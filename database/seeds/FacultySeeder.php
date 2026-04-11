<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FacultySeeder extends Seeder
{
    public function run()
    {
        // Extra students for class list demo
        $students = [
            ['student_no' => '2024001', 'name' => 'Reyes, Juan Miguel',   'sex' => 'Male',   'age' => 20, 'program' => 'BSCS', 'year_level' => '4th Year', 'school_year' => '2025-2026', 'semester' => '2nd Semester'],
            ['student_no' => '2024002', 'name' => 'Santos, Maria Clara',  'sex' => 'Female', 'age' => 21, 'program' => 'BSCS', 'year_level' => '4th Year', 'school_year' => '2025-2026', 'semester' => '2nd Semester'],
            ['student_no' => '2024003', 'name' => 'De Leon, Carlos',      'sex' => 'Male',   'age' => 22, 'program' => 'BSIT', 'year_level' => '4th Year', 'school_year' => '2025-2026', 'semester' => '2nd Semester'],
            ['student_no' => '2024004', 'name' => 'Flores, Patricia',     'sex' => 'Female', 'age' => 20, 'program' => 'BSCS', 'year_level' => '4th Year', 'school_year' => '2025-2026', 'semester' => '2nd Semester'],
            ['student_no' => '2024005', 'name' => 'Garcia, Jose',         'sex' => 'Male',   'age' => 21, 'program' => 'BSIT', 'year_level' => '4th Year', 'school_year' => '2025-2026', 'semester' => '2nd Semester'],
            ['student_no' => '2024006', 'name' => 'Mendoza, Ana',         'sex' => 'Female', 'age' => 20, 'program' => 'BSCS', 'year_level' => '1st Year', 'school_year' => '2025-2026', 'semester' => '2nd Semester'],
            ['student_no' => '2024007', 'name' => 'Torres, Roberto',      'sex' => 'Male',   'age' => 18, 'program' => 'BSCS', 'year_level' => '1st Year', 'school_year' => '2025-2026', 'semester' => '2nd Semester'],
            ['student_no' => '2024008', 'name' => 'Ramos, Cristina',      'sex' => 'Female', 'age' => 19, 'program' => 'BSCS', 'year_level' => '1st Year', 'school_year' => '2025-2026', 'semester' => '2nd Semester'],
        ];

        $hasProgramColumn = Schema::hasColumn('students', 'program');
        $hasCourseIdColumn = Schema::hasColumn('students', 'course_id');
        $hasYearLevelColumn = Schema::hasColumn('students', 'year_level');
        $hasYearBlockIdColumn = Schema::hasColumn('students', 'year_block_id');
        $hasSchoolYearColumn = Schema::hasColumn('students', 'school_year');
        $hasSemesterColumn = Schema::hasColumn('students', 'semester');
        $hasAcademicTermIdColumn = Schema::hasColumn('students', 'academic_term_id');

        $courseIds = $hasCourseIdColumn && Schema::hasTable('courses')
            ? DB::table('courses')->pluck('id', 'code')->toArray()
            : [];

        $yearBlockIds = [];
        if ($hasYearBlockIdColumn && Schema::hasTable('year_blocks')) {
            $yearBlockIds = DB::table('year_blocks')->pluck('id', 'label')->toArray();
        }

        $academicTermIds = [];
        if ($hasAcademicTermIdColumn && Schema::hasTable('academic_terms')) {
            $academicTermIds = DB::table('academic_terms')
                ->select('id', 'school_year', 'term')
                ->get()
                ->mapWithKeys(function ($row) {
                    $key = strtolower(trim((string) $row->school_year) . '|' . trim((string) $row->term));

                    return [$key => (int) $row->id];
                })
                ->all();
        }

        foreach ($students as $s) {
            $payload = [
                'name' => $s['name'],
                'sex' => $s['sex'],
                'age' => $s['age'],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if ($hasProgramColumn) {
                $payload['program'] = $s['program'];
            }

            if ($hasCourseIdColumn) {
                $payload['course_id'] = $courseIds[$s['program']] ?? null;
            }

            if ($hasYearLevelColumn) {
                $payload['year_level'] = $s['year_level'];
            }

            if ($hasYearBlockIdColumn) {
                $payload['year_block_id'] = $yearBlockIds[$s['year_level']] ?? null;
            }

            if ($hasSchoolYearColumn) {
                $payload['school_year'] = $s['school_year'];
            }

            if ($hasSemesterColumn) {
                $payload['semester'] = $s['semester'];
            }

            if ($hasAcademicTermIdColumn) {
                $termKey = strtolower(trim((string) $s['school_year']) . '|' . trim((string) $s['semester']));
                $payload['academic_term_id'] = $academicTermIds[$termKey] ?? null;
            }

            DB::table('students')->updateOrInsert(
                ['student_no' => $s['student_no']],
                $payload
            );
        }

        // Enrol all students in all faculty subjects
        $facultyCodes = ['SAM125', 'OOP111', 'SPI128', 'UTS12'];
        $subjectIds = DB::table('subjects')->whereIn('code', $facultyCodes)->pluck('id');
        $allStudentIds = DB::table('students')->pluck('id');

        foreach ($allStudentIds as $studentId) {
            foreach ($subjectIds as $subjectId) {
                DB::table('student_subject')->updateOrInsert(
                    ['student_id' => $studentId, 'subject_id' => $subjectId],
                    ['student_id' => $studentId, 'subject_id' => $subjectId]
                );
            }
        }

        // Faculty Evaluation data
        $evaluations = [
            ['code' => 'SAM125', 'section' => 'BSCS 4-B', 'mean_score' => 4.8],
            ['code' => 'OOP111', 'section' => 'BSCS 4-B', 'mean_score' => 4.2],
            ['code' => 'SPI128', 'section' => 'BSIT 4-A', 'mean_score' => 4.5],
            ['code' => 'UTS12',  'section' => 'BSCS 1-C', 'mean_score' => 4.3],
        ];

        foreach ($evaluations as $ev) {
            $subject = DB::table('subjects')->where('code', $ev['code'])->first();
            if ($subject) {
                DB::table('faculty_evaluations')->updateOrInsert(
                    ['subject_id' => $subject->id, 'section' => $ev['section']],
                    [
                        'subject_id' => $subject->id,
                        'section'    => $ev['section'],
                        'mean_score' => $ev['mean_score'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
