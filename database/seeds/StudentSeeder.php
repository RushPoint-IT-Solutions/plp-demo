<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentSeeder extends Seeder
{
    public function run()
    {
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
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }
}
