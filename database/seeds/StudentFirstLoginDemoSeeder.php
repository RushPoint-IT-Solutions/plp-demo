<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class StudentFirstLoginDemoSeeder extends Seeder
{
    public function run()
    {
        $now = now();

        $payload = [
            'name' => 'Reset, Student Demo',
            'sex' => 'Female',
            'age' => 19,
            'college' => 'College of Computer Studies',
            'scholarship' => 'FREE EDUCATION',
            'registration_no' => 'REG-2026-000001',
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
            $payload['curriculum'] = 'BSIT 2025-2026';
        }

        if (Schema::hasColumn('students', 'year_level')) {
            $payload['year_level'] = '1st Year';
        }

        if (Schema::hasColumn('students', 'year_block_id') && Schema::hasTable('year_blocks')) {
            $payload['year_block_id'] = DB::table('year_blocks')
                ->where('label', '1st Year')
                ->value('id');
        }

        if (Schema::hasColumn('students', 'school_year')) {
            $payload['school_year'] = '2026-2027';
        }

        if (Schema::hasColumn('students', 'semester')) {
            $payload['semester'] = '1st Semester';
        }

        if (Schema::hasColumn('students', 'academic_term_id') && Schema::hasTable('academic_terms')) {
            $payload['academic_term_id'] = DB::table('academic_terms')
                ->where('school_year', '2026-2027')
                ->whereRaw("LOWER(TRIM(term)) IN ('first', 'first semester', '1st semester')")
                ->value('id');
        }

        DB::table('students')->updateOrInsert(
            ['student_no' => '2026A00001'],
            $payload
        );

        $student = DB::table('students')->where('student_no', '2026A00001')->first();
        if (!$student) {
            return;
        }

        DB::table('users')->updateOrInsert(
            ['username' => '2026A00001'],
            [
                'name' => 'Reset, Student Demo',
                'email' => 'student.reset@plp.local',
                'password' => Hash::make('PLP-2026A00001'),
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
