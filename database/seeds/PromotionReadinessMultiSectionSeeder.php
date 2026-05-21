<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class PromotionReadinessMultiSectionSeeder extends Seeder
{
    public function run()
    {
        if (app()->environment('production')) {
            throw new RuntimeException('PromotionReadinessMultiSectionSeeder cannot run in production.');
        }

        $course = DB::table('courses')->where('code', 'BSA')->first() ?: DB::table('courses')->orderBy('code')->first();
        if (!$course) {
            $this->command->warn('No course found for promotion readiness sample.');
            return;
        }

        $termId = $this->termId('2026-2027', 'Second Semester');
        $now = now();
        $yearSubjects = [
            1 => [
                ['GE104', 'Purposive Communication', 3],
                ['GE105', 'Art Appreciation', 3],
                ['ACC103', 'Intermediate Accounting 1', 6],
            ],
            2 => [
                ['ACC201', 'Intermediate Accounting 2', 6],
                ['LAW201', 'Law on Obligations and Contracts', 3],
                ['TAX201', 'Income Taxation', 3],
            ],
            3 => [
                ['ACC301', 'Advanced Accounting 1', 6],
                ['AUD301', 'Auditing and Assurance Principles', 3],
                ['TAX301', 'Business Taxation', 3],
            ],
        ];

        foreach ([1, 2, 3] as $yearNumber) {
            $yearBlockId = $this->yearBlockId($yearNumber);
            foreach (['A', 'B'] as $sectionSuffix) {
                $section = (string) $course->code . '-' . $yearNumber . $sectionSuffix;
                $subjectIds = [];
                foreach ($yearSubjects[$yearNumber] as $subject) {
                    $subjectIds[] = $this->subjectOfferingId($subject, (int) $course->id, $termId, $section, $now);
                }

                for ($index = 1; $index <= 30; $index++) {
                    $studentNo = sprintf('26-%d%s%03d', 700 + $yearNumber, $sectionSuffix, $index);
                    $studentId = $this->studentId($studentNo, $yearNumber, $sectionSuffix, $index, $course, $yearBlockId, $termId, $now);

                    DB::table('student_section_assignments')->updateOrInsert([
                        'academic_term_id' => $termId,
                        'student_id' => $studentId,
                    ], [
                        'course_id' => (int) $course->id,
                        'year_block_id' => $yearBlockId,
                        'section' => $section,
                        'status' => 'active',
                        'approval_status' => 'auto_approved',
                        'flags' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    foreach ($subjectIds as $position => $subjectId) {
                        DB::table('student_subject')->updateOrInsert([
                            'student_id' => $studentId,
                            'subject_id' => $subjectId,
                        ], ['created_at' => $now, 'updated_at' => $now]);

                        $average = 86 + (($index + $position) % 8);
                        $remarks = 'Passed';
                        if ($index % 15 === 0 && $position === 1) {
                            $average = 72;
                            $remarks = 'Failed';
                        } elseif ($index % 11 === 0 && $position === 2) {
                            $average = null;
                            $remarks = 'Incomplete';
                        }

                        $this->grade($studentId, $subjectId, $average, $remarks, $now);
                    }
                }
            }
        }

        $this->command->info('Promotion readiness samples seeded: 6 sections, 180 students, 30 students per section.');
    }

    private function termId(string $schoolYear, string $term): int
    {
        $payload = [
            'canonical_key' => strtolower($schoolYear . ':' . $term),
            'created_at' => now(),
            'updated_at' => now(),
        ];
        if (Schema::hasColumn('academic_terms', 'status')) {
            $payload['status'] = 'Open for Enrollment';
        }

        DB::table('academic_terms')->updateOrInsert(['school_year' => $schoolYear, 'term' => $term], $payload);

        return (int) DB::table('academic_terms')->where('school_year', $schoolYear)->where('term', $term)->value('id');
    }

    private function yearBlockId(int $yearNumber): int
    {
        return (int) DB::table('year_blocks')
            ->where('label', 'like', '%' . $yearNumber . '%')
            ->orderBy('id')
            ->value('id');
    }

    private function subjectOfferingId(array $subject, int $courseId, int $termId, string $section, $now): int
    {
        $match = [
            'code' => $subject[0],
            'course_id' => $courseId,
            'academic_term_id' => $termId,
            'year_section' => $section,
        ];
        if (Schema::hasColumn('subjects', 'is_subject_file_record')) {
            $match['is_subject_file_record'] = 0;
        }

        $payload = $this->columns('subjects', [
            'name' => $subject[1],
            'units' => $subject[2],
            'lec' => $subject[2],
            'lab' => 0,
            'hours' => $subject[2],
            'course_type' => strpos($subject[0], 'GE') === 0 ? 'GE' : 'Major',
            'is_core' => strpos($subject[0], 'GE') === 0 ? 1 : 0,
            'is_applied' => 0,
            'is_specialized' => strpos($subject[0], 'GE') === 0 ? 0 : 1,
            'is_subject_file_record' => 0,
            'days' => 'MWF',
            'time_start' => '09:00',
            'time_end' => '10:00',
            'credited_tuition_units' => $subject[2],
            'load_hours' => $subject[2],
            'added_by' => 'PromotionReadinessMultiSectionSeeder',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('subjects')->updateOrInsert($this->columns('subjects', $match), $payload);

        return (int) DB::table('subjects')->where($this->columns('subjects', $match))->value('id');
    }

    private function studentId(string $studentNo, int $yearNumber, string $sectionSuffix, int $index, $course, int $yearBlockId, int $termId, $now): int
    {
        $name = sprintf('Readiness %d%s, Student %02d', $yearNumber, $sectionSuffix, $index);
        $payload = $this->columns('students', [
            'name' => $name,
            'sex' => $index % 2 ? 'Female' : 'Male',
            'age' => 18 + $yearNumber,
            'college' => 'College of Business and Accountancy',
            'program' => (string) $course->code,
            'curriculum' => (string) $course->code . ' Promotion Readiness',
            'year_level' => $yearNumber . ' Year',
            'scholarship' => 'UNIFIED FINANCIAL ASSISTANCE FOR TERTIARY EDUCATION',
            'registration_no' => 'PROMO-' . $studentNo,
            'school_year' => '2026-2027',
            'semester' => 'Second Semester',
            'course_id' => (int) $course->id,
            'year_block_id' => $yearBlockId,
            'academic_term_id' => $termId,
            'is_withdrawn' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('students')->updateOrInsert(['student_no' => $studentNo], $payload);
        $studentId = (int) DB::table('students')->where('student_no', $studentNo)->value('id');

        DB::table('users')->updateOrInsert(['username' => $studentNo], $this->columns('users', [
            'name' => $name,
            'email' => strtolower(str_replace('-', '', $studentNo)) . '@student.plp.local',
            'password' => Hash::make('student'),
            'module' => 'student',
            'force_password_reset' => false,
            'student_id' => $studentId,
            'created_at' => $now,
            'updated_at' => $now,
        ]));

        return $studentId;
    }

    private function grade(int $studentId, int $subjectId, ?float $average, string $remarks, $now): void
    {
        DB::table('student_subject_grades')->updateOrInsert([
            'student_id' => $studentId,
            'subject_id' => $subjectId,
        ], $this->columns('student_subject_grades', [
            'prelim' => null,
            'midterm' => $average,
            'final' => $average,
            'final_average' => $average,
            'remarks' => $remarks,
            'status' => 'Posted',
            'draft_saved_at' => $now,
            'midterm_posted_at' => $now,
            'final_posted_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]));
    }

    private function columns(string $table, array $payload): array
    {
        $columns = Schema::getColumnListing($table);
        return array_filter($payload, function ($value, $key) use ($columns) {
            return in_array($key, $columns, true);
        }, ARRAY_FILTER_USE_BOTH);
    }
}
