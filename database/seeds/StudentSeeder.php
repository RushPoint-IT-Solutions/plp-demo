<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    public function run()
    {
        $now = now();

        DB::table('students')->updateOrInsert([
            'student_no'      => '1234567891012',
        ], [
            'name'            => 'Austero, Andrea Jane',
            'sex'             => 'Female',
            'age'             => 21,
            'college'         => '',
            'program'         => 'BSIT',
            'curriculum'      => 'BSIT 2018-2019',
            'year_level'      => '4th Year',
            'scholarship'     => 'FREE EDUCATION',
            'registration_no' => '12232345431',
            'school_year'     => '2025-2026',
            'semester'        => '2nd Semester',
            'created_at'      => $now,
            'updated_at'      => $now,
        ]);

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
