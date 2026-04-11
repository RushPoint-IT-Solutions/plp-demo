<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class StudentSeeder extends Seeder
{
    public function run()
    {
        $now = now();

        $payload = [
            'name' => 'Austero, Andrea Jane',
            'sex' => 'Female',
            'age' => 21,
            'college' => '',
            'scholarship' => 'FREE EDUCATION',
            'registration_no' => '12232345431',
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if (Schema::hasColumn('students', 'program')) {
            $payload['program'] = 'BSIT';
        }

        if (Schema::hasColumn('students', 'course_id') && Schema::hasTable('courses')) {
            $payload['course_id'] = DB::table('courses')
                ->where('code', 'BSIT')
                ->orWhere('name', 'LIKE', '%Information Technology%')
                ->value('id');
        }

        if (Schema::hasColumn('students', 'curriculum')) {
            $payload['curriculum'] = 'BSIT 2018-2019';
        }

        if (Schema::hasColumn('students', 'year_level')) {
            $payload['year_level'] = '4th Year';
        }

        if (Schema::hasColumn('students', 'year_block_id') && Schema::hasTable('year_blocks')) {
            $payload['year_block_id'] = DB::table('year_blocks')
                ->where('label', '4th Year')
                ->value('id');
        }

        if (Schema::hasColumn('students', 'school_year')) {
            $payload['school_year'] = '2025-2026';
        }

        if (Schema::hasColumn('students', 'semester')) {
            $payload['semester'] = '2nd Semester';
        }

        if (Schema::hasColumn('students', 'academic_term_id') && Schema::hasTable('academic_terms')) {
            $payload['academic_term_id'] = DB::table('academic_terms')
                ->where('school_year', '2025-2026')
                ->whereRaw("LOWER(TRIM(term)) IN ('second', 'second semester', '2nd semester')")
                ->value('id');
        }

        DB::table('students')->updateOrInsert([
            'student_no'      => '1234567891012',
        ], $payload);

        $student = DB::table('students')->where('student_no', '1234567891012')->first();
        if (!$student) {
            return;
        }

        $defaultPassword = 'PLP-' . $student->student_no;

        DB::table('users')->updateOrInsert(
            ['username' => $student->student_no],
            [
                'name' => $student->name,
                'password' => Hash::make($defaultPassword),
                'module' => 'student',
                'force_password_reset' => true,
                'student_id' => $student->id,
                'faculty_id' => null,
                'registrar_id' => null,
                'applicant_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}
