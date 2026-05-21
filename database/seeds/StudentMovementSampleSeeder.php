<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class StudentMovementSampleSeeder extends Seeder
{
    public function run()
    {
        if (app()->environment('production')) {
            throw new RuntimeException('StudentMovementSampleSeeder cannot run in production.');
        }

        $now = now();
        $course = DB::table('courses')->where('code', 'BSA')->first();
        if (!$course) {
            $course = DB::table('courses')->orderBy('code')->first();
        }

        if (!$course) {
            $this->command->warn('No course found. Skipping student movement samples.');
            return;
        }

        $termId = $this->termId('2026-2027', 'Second Semester');
        $schoolYear = '2026-2027';
        $semester = 'Second Semester';

        $yearRows = [
            1 => [
                'year_label' => '1st Year',
                'section' => $course->code . '-1A',
                'students' => [
                    ['student_no' => '26-91001', 'name' => 'Alcantara, Mikaela Reyes', 'sex' => 'Female', 'average' => 91.25],
                    ['student_no' => '26-91002', 'name' => 'Bautista, Carlo Santos', 'sex' => 'Male', 'average' => 88.50],
                    ['student_no' => '26-91003', 'name' => 'Cruz, Hannah Mae Lopez', 'sex' => 'Female', 'average' => 84.75],
                ],
                'subjects' => [
                    ['code' => 'GE104', 'name' => 'Purposive Communication', 'units' => 3, 'lec' => 3, 'lab' => 0, 'type' => 'GE'],
                    ['code' => 'GE105', 'name' => 'Art Appreciation', 'units' => 3, 'lec' => 3, 'lab' => 0, 'type' => 'GE'],
                    ['code' => 'ACC103', 'name' => 'Intermediate Accounting 1', 'units' => 6, 'lec' => 6, 'lab' => 0, 'type' => 'Major'],
                ],
            ],
            2 => [
                'year_label' => '2nd Year',
                'section' => $course->code . '-2A',
                'students' => [
                    ['student_no' => '25-92001', 'name' => 'Dizon, Patrick Luis', 'sex' => 'Male', 'average' => 90.00],
                    ['student_no' => '25-92002', 'name' => 'Evangelista, Sofia Anne', 'sex' => 'Female', 'average' => 86.25],
                    ['student_no' => '25-92003', 'name' => 'Flores, Adrian Miguel', 'sex' => 'Male', 'average' => 82.50],
                ],
                'subjects' => [
                    ['code' => 'ACC201', 'name' => 'Intermediate Accounting 2', 'units' => 6, 'lec' => 6, 'lab' => 0, 'type' => 'Major'],
                    ['code' => 'LAW201', 'name' => 'Law on Obligations and Contracts', 'units' => 3, 'lec' => 3, 'lab' => 0, 'type' => 'Major'],
                    ['code' => 'TAX201', 'name' => 'Income Taxation', 'units' => 3, 'lec' => 3, 'lab' => 0, 'type' => 'Major'],
                ],
            ],
            3 => [
                'year_label' => '3rd Year',
                'section' => $course->code . '-3A',
                'students' => [
                    ['student_no' => '24-93001', 'name' => 'Garcia, Alyssa Nicole', 'sex' => 'Female', 'average' => 92.00],
                    ['student_no' => '24-93002', 'name' => 'Hernandez, Marco Rafael', 'sex' => 'Male', 'average' => 87.75],
                    ['student_no' => '24-93003', 'name' => 'Ilagan, Beatrice Joy', 'sex' => 'Female', 'average' => 83.25],
                ],
                'subjects' => [
                    ['code' => 'ACC301', 'name' => 'Advanced Accounting 1', 'units' => 6, 'lec' => 6, 'lab' => 0, 'type' => 'Major'],
                    ['code' => 'AUD301', 'name' => 'Auditing and Assurance Principles', 'units' => 3, 'lec' => 3, 'lab' => 0, 'type' => 'Major'],
                    ['code' => 'TAX301', 'name' => 'Business Taxation', 'units' => 3, 'lec' => 3, 'lab' => 0, 'type' => 'Major'],
                ],
            ],
        ];

        foreach ($yearRows as $yearNumber => $yearData) {
            $yearBlockId = $this->yearBlockId($yearData['year_label']);
            $subjectIds = [];

            foreach ($yearData['subjects'] as $subjectRow) {
                $subjectIds[] = $this->upsertSubjectOffering(
                    $subjectRow,
                    (int) $course->id,
                    $termId,
                    $schoolYear,
                    $semester,
                    $yearData['section'],
                    $now
                );
            }

            foreach ($yearData['students'] as $studentRow) {
                $studentId = $this->upsertStudent(
                    $studentRow,
                    $course,
                    $yearBlockId,
                    $yearData['year_label'],
                    $termId,
                    $schoolYear,
                    $semester,
                    $now
                );

                foreach ($subjectIds as $subjectId) {
                    DB::table('student_subject')->updateOrInsert([
                        'student_id' => $studentId,
                        'subject_id' => $subjectId,
                    ], [
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    $this->upsertGrade($studentId, $subjectId, (float) $studentRow['average'], $now);
                }
            }
        }

        $this->command->info('Student movement samples seeded: 9 students across 1st, 2nd, and 3rd year with posted grades.');
    }

    private function termId(string $schoolYear, string $semester): ?int
    {
        if (!Schema::hasTable('academic_terms')) {
            return null;
        }

        $payload = [
            'canonical_key' => strtolower($schoolYear . ':' . $semester),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('academic_terms', 'status')) {
            $payload['status'] = 'Open for Enrollment';
        }

        DB::table('academic_terms')->updateOrInsert([
            'school_year' => $schoolYear,
            'term' => $semester,
        ], $payload);

        return (int) DB::table('academic_terms')
            ->where('school_year', $schoolYear)
            ->where('term', $semester)
            ->value('id');
    }

    private function yearBlockId(string $label): ?int
    {
        if (!Schema::hasTable('year_blocks')) {
            return null;
        }

        $id = DB::table('year_blocks')->where('label', $label)->value('id');
        if ($id) {
            return (int) $id;
        }

        $number = (int) substr($label, 0, 1);
        return (int) DB::table('year_blocks')
            ->where('label', 'like', '%' . $number . '%')
            ->value('id') ?: null;
    }

    private function upsertSubjectOffering(array $subjectRow, int $courseId, ?int $termId, string $schoolYear, string $semester, string $section, $now): int
    {
        $match = [
            'code' => $subjectRow['code'],
            'course_id' => $courseId,
            'academic_term_id' => $termId,
            'year_section' => $section,
        ];

        if (Schema::hasColumn('subjects', 'is_subject_file_record')) {
            $match['is_subject_file_record'] = 0;
        }

        $payload = [
            'name' => $subjectRow['name'],
            'units' => $subjectRow['units'],
            'lec' => $subjectRow['lec'],
            'lab' => $subjectRow['lab'],
            'hours' => $subjectRow['units'],
            'course_type' => $subjectRow['type'],
            'is_core' => $subjectRow['type'] === 'GE' ? 1 : 0,
            'is_applied' => 0,
            'is_specialized' => $subjectRow['type'] === 'Major' ? 1 : 0,
            'school_year' => $schoolYear,
            'semester' => $semester,
            'course' => null,
            'days' => 'MWF',
            'time_start' => '09:00',
            'time_end' => '10:00',
            'room' => null,
            'faculty_id' => null,
            'credited_tuition_units' => $subjectRow['units'],
            'load_hours' => $subjectRow['units'],
            'added_by' => 'StudentMovementSampleSeeder',
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if (Schema::hasColumn('subjects', 'is_subject_file_record')) {
            $payload['is_subject_file_record'] = 0;
        }

        $match = $this->onlyExistingColumns('subjects', $match);
        $payload = $this->onlyExistingColumns('subjects', $payload);

        DB::table('subjects')->updateOrInsert($match, $payload);

        return (int) DB::table('subjects')
            ->where($match)
            ->value('id');
    }

    private function upsertStudent(array $studentRow, $course, ?int $yearBlockId, string $yearLabel, ?int $termId, string $schoolYear, string $semester, $now): int
    {
        $payload = [
            'name' => $studentRow['name'],
            'sex' => $studentRow['sex'],
            'age' => 20,
            'college' => 'College of Business and Accountancy',
            'program' => (string) $course->code,
            'curriculum' => (string) $course->code . ' Sample Curriculum',
            'year_level' => $yearLabel,
            'scholarship' => 'UNIFIED FINANCIAL ASSISTANCE FOR TERTIARY EDUCATION',
            'registration_no' => 'MOVE-' . $studentRow['student_no'],
            'school_year' => $schoolYear,
            'semester' => $semester,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if (Schema::hasColumn('students', 'course_id')) {
            $payload['course_id'] = (int) $course->id;
        }
        if (Schema::hasColumn('students', 'year_block_id')) {
            $payload['year_block_id'] = $yearBlockId;
        }
        if (Schema::hasColumn('students', 'academic_term_id')) {
            $payload['academic_term_id'] = $termId;
        }
        if (Schema::hasColumn('students', 'status')) {
            $payload['status'] = 'Active';
        }
        if (Schema::hasColumn('students', 'is_withdrawn')) {
            $payload['is_withdrawn'] = 0;
        }

        $payload = $this->onlyExistingColumns('students', $payload);

        DB::table('students')->updateOrInsert([
            'student_no' => $studentRow['student_no'],
        ], $payload);

        $studentId = (int) DB::table('students')->where('student_no', $studentRow['student_no'])->value('id');

        if (Schema::hasTable('student_profiles')) {
            $profilePayload = [
                'student_id' => $studentId,
                'first_name' => $this->firstName($studentRow['name']),
                'last_name' => $this->lastName($studentRow['name']),
                'gender' => $studentRow['sex'],
                'nationality' => 'Filipino',
                'civil_status' => 'Single',
                'mobile_number' => '0917000' . substr($studentRow['student_no'], -4),
                'student_email' => strtolower(str_replace('-', '', $studentRow['student_no'])) . '@student.plp.local',
                'present_municipality' => 'Pasig',
                'present_province' => 'Metro Manila',
                'present_region' => 'NCR',
                'profile_complete' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            DB::table('student_profiles')->updateOrInsert([
                'student_no' => $studentRow['student_no'],
            ], $this->onlyExistingColumns('student_profiles', $profilePayload));
        }

        if (Schema::hasTable('users')) {
            $userPayload = [
                'name' => $studentRow['name'],
                'email' => strtolower(str_replace('-', '', $studentRow['student_no'])) . '@student.plp.local',
                'password' => Hash::make('student'),
                'module' => 'student',
                'force_password_reset' => false,
                'student_id' => $studentId,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            DB::table('users')->updateOrInsert([
                'username' => $studentRow['student_no'],
            ], $this->onlyExistingColumns('users', $userPayload));
        }

        return $studentId;
    }

    private function upsertGrade(int $studentId, int $subjectId, float $average, $now): void
    {
        $payload = [
            'prelim' => null,
            'midterm' => $average,
            'final' => $average,
            'final_average' => $average,
            'remarks' => $average >= 75.0 ? 'Passed' : 'Failed',
            'created_at' => $now,
            'updated_at' => $now,
        ];

        foreach (['status' => 'Posted', 'midterm_posted_at' => $now, 'final_posted_at' => $now, 'draft_saved_at' => $now] as $column => $value) {
            if (Schema::hasColumn('student_subject_grades', $column)) {
                $payload[$column] = $value;
            }
        }

        $payload = $this->onlyExistingColumns('student_subject_grades', $payload);

        DB::table('student_subject_grades')->updateOrInsert([
            'student_id' => $studentId,
            'subject_id' => $subjectId,
        ], $payload);
    }

    private function onlyExistingColumns(string $table, array $payload): array
    {
        $columns = Schema::getColumnListing($table);

        return array_filter($payload, function ($value, $key) use ($columns) {
            return in_array($key, $columns, true);
        }, ARRAY_FILTER_USE_BOTH);
    }

    private function firstName(string $name): string
    {
        $parts = explode(',', $name, 2);
        return trim($parts[1] ?? $name);
    }

    private function lastName(string $name): string
    {
        $parts = explode(',', $name, 2);
        return trim($parts[0] ?? $name);
    }
}
