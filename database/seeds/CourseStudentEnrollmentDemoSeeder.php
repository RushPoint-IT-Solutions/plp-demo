<?php

use App\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class CourseStudentEnrollmentDemoSeeder extends Seeder
{
    private const TARGET_STUDENTS_PER_COURSE = 30;
    private const SCHOOL_YEAR = '2026-2027';
    private const SEMESTER = 'First Semester';

    public function run()
    {
        if (app()->environment('production')) {
            throw new RuntimeException('CourseStudentEnrollmentDemoSeeder cannot run in production.');
        }

        $now = now();
        $academicTermId = $this->ensureAcademicTerm($now);
        $yearBlockId = $this->ensureYearBlock($now);

        DB::transaction(function () use ($now, $academicTermId, $yearBlockId) {
            if (Schema::hasTable('student_subject')) {
                DB::table('student_subject')->delete();
            }

            $courses = DB::table('courses')
                ->orderBy('code')
                ->get(['id', 'code', 'name', 'college_id']);

            foreach ($courses as $course) {
                $existingCount = DB::table('students')
                    ->where('course_id', $course->id)
                    ->count();

                $needed = max(0, self::TARGET_STUDENTS_PER_COURSE - $existingCount);

                for ($i = 1; $i <= $needed; $i++) {
                    $sequence = $existingCount + $i;
                    $studentNo = Student::generateStudentNo(2026);
                    $name = $this->studentName($course->code, $sequence);
                    $firstName = $this->firstName($sequence);
                    $lastName = $this->lastName($sequence) . ' ' . $course->code;

                    $studentPayload = [
                        'student_no' => $studentNo,
                        'name' => $name,
                        'sex' => $sequence % 2 === 0 ? 'Female' : 'Male',
                        'age' => 18 + ($sequence % 5),
                        'college' => $this->collegeName($course->college_id),
                        'curriculum' => $course->code . ' ' . self::SCHOOL_YEAR,
                        'scholarship' => 'UNIFIED FINANCIAL ASSISTANCE FOR TERTIARY EDUCATION',
                        'registration_no' => 'REG-' . str_replace('-', '', self::SCHOOL_YEAR) . '-' . str_pad((string) $studentNo, 8, '0', STR_PAD_LEFT),
                        'academic_term_id' => $academicTermId,
                        'course_id' => $course->id,
                        'year_block_id' => $yearBlockId,
                        'is_withdrawn' => false,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    if (Schema::hasColumn('students', 'program')) {
                        $studentPayload['program'] = $course->name;
                    }
                    if (Schema::hasColumn('students', 'year_level')) {
                        $studentPayload['year_level'] = '1st Year';
                    }
                    if (Schema::hasColumn('students', 'school_year')) {
                        $studentPayload['school_year'] = self::SCHOOL_YEAR;
                    }
                    if (Schema::hasColumn('students', 'semester')) {
                        $studentPayload['semester'] = self::SEMESTER;
                    }

                    $studentId = DB::table('students')->insertGetId($studentPayload);

                    if (Schema::hasTable('student_profiles')) {
                        DB::table('student_profiles')->updateOrInsert(
                            ['student_id' => $studentId],
                            [
                                'student_no' => $studentNo,
                                'first_name' => $firstName,
                                'last_name' => $lastName,
                                'middle_name' => 'Demo',
                                'gender' => $sequence % 2 === 0 ? 'Female' : 'Male',
                                'nationality' => 'Filipino',
                                'date_of_birth' => now()->subYears(18 + ($sequence % 5))->toDateString(),
                                'civil_status' => 'Single',
                                'mobile_number' => '09' . str_pad((string) (170000000 + $studentId), 9, '0', STR_PAD_LEFT),
                                'student_email' => strtolower(str_replace(' ', '.', $firstName . '.' . $lastName)) . '@plp.edu.ph',
                                'present_street' => $sequence . ' Demo Street',
                                'present_barangay' => 'Kapasigan',
                                'present_municipality' => 'Pasig City',
                                'present_province' => 'Metro Manila',
                                'present_region' => 'NCR',
                                'same_as_present' => true,
                                'profile_complete' => true,
                                'updated_at' => $now,
                                'created_at' => $now,
                            ]
                        );
                    }

                    if (Schema::hasTable('users')) {
                        DB::table('users')->updateOrInsert(
                            ['username' => $studentNo],
                            [
                                'name' => $name,
                                'email' => strtolower($studentNo) . '@plp.edu.ph',
                                'password' => Hash::make($studentNo),
                                'module' => 'student',
                                'force_password_reset' => true,
                                'student_id' => $studentId,
                                'updated_at' => $now,
                                'created_at' => $now,
                            ]
                        );
                    }
                }

                $subjectIds = DB::table('subjects')
                    ->where('course_id', $course->id)
                    ->orderBy('code')
                    ->pluck('id');

                $students = DB::table('students')
                    ->where('course_id', $course->id)
                    ->orderBy('student_no')
                    ->get(['id']);

                foreach ($students as $student) {
                    foreach ($subjectIds as $subjectId) {
                        DB::table('student_subject')->updateOrInsert(
                            ['student_id' => $student->id, 'subject_id' => $subjectId],
                            ['created_at' => $now, 'updated_at' => $now]
                        );
                    }
                }
            }
        });
    }

    private function ensureAcademicTerm($now): int
    {
        if (!Schema::hasTable('academic_terms')) {
            return 0;
        }

        DB::table('academic_terms')->updateOrInsert(
            ['canonical_key' => strtolower(self::SCHOOL_YEAR . '|' . self::SEMESTER)],
            [
                'school_year' => self::SCHOOL_YEAR,
                'term' => self::SEMESTER,
                'status' => 'Open for Enrollment',
                'opened_at' => $now,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        return (int) DB::table('academic_terms')
            ->where('canonical_key', strtolower(self::SCHOOL_YEAR . '|' . self::SEMESTER))
            ->value('id');
    }

    private function ensureYearBlock($now): int
    {
        if (!Schema::hasTable('year_blocks')) {
            return 0;
        }

        DB::table('year_blocks')->updateOrInsert(
            ['label' => '1st Year'],
            ['updated_at' => $now, 'created_at' => $now]
        );

        return (int) DB::table('year_blocks')->where('label', '1st Year')->value('id');
    }

    private function collegeName($collegeId): string
    {
        if (!$collegeId || !Schema::hasTable('colleges')) {
            return '';
        }

        return (string) DB::table('colleges')->where('id', $collegeId)->value('name');
    }

    private function studentName(string $courseCode, int $sequence): string
    {
        return strtoupper($this->lastName($sequence) . ' ' . $courseCode . ', ' . $this->firstName($sequence) . ' DEMO');
    }

    private function firstName(int $sequence): string
    {
        $names = ['Andrea', 'Miguel', 'Sofia', 'Daniel', 'Bianca', 'Joshua', 'Patricia', 'Carlos', 'Nicole', 'Gabriel'];
        return $names[($sequence - 1) % count($names)];
    }

    private function lastName(int $sequence): string
    {
        $names = ['Santos', 'Reyes', 'Cruz', 'Garcia', 'Mendoza', 'Torres', 'Flores', 'Ramos', 'Dizon', 'Navarro'];
        return $names[(int) floor(($sequence - 1) / 10) % count($names)] . str_pad((string) $sequence, 2, '0', STR_PAD_LEFT);
    }
}
