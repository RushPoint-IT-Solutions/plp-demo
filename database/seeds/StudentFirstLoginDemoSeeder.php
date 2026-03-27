<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentFirstLoginDemoSeeder extends Seeder
{
    public function run()
    {
        $now = now();

        DB::table('students')->updateOrInsert(
            ['student_no' => '2026A00001'],
            [
                'name' => 'Reset, Student Demo',
                'sex' => 'Female',
                'age' => 19,
                'college' => 'College of Computer Studies',
                'program' => 'BSIT',
                'curriculum' => 'BSIT 2025-2026',
                'year_level' => '1st Year',
                'scholarship' => 'FREE EDUCATION',
                'registration_no' => 'REG-2026-000001',
                'school_year' => '2026-2027',
                'semester' => '1st Semester',
                'created_at' => $now,
                'updated_at' => $now,
            ]
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
