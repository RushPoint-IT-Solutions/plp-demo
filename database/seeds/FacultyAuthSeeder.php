<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class FacultyAuthSeeder extends Seeder
{
    public function run()
    {
        $now = now();

        DB::table('faculties')->updateOrInsert(
            ['code' => 'FAC-001'],
            [
                'name' => 'Abejo, M.',
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        $faculty = DB::table('faculties')->where('code', 'FAC-001')->first();

        DB::table('users')->updateOrInsert(
            ['username' => 'faculty'],
            [
                'name' => 'Faculty Demo User',
                'email' => 'faculty.demo@plp.local',
                'password' => Hash::make('faculty'),
                'module' => 'faculty',
                'force_password_reset' => false,
                'student_id' => null,
                'faculty_id' => $faculty ? $faculty->id : null,
                'registrar_id' => null,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
    }
}
