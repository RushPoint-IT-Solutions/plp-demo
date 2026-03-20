<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegistrarAuthSeeder extends Seeder
{
    public function run()
    {
        $now = now();

        DB::table('registrars')->updateOrInsert(
            ['code' => 'REG-001'],
            [
                'name' => 'Registrar Demo User',
                'email' => 'registrar.demo@plp.local',
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        $registrar = DB::table('registrars')->where('code', 'REG-001')->first();

        DB::table('users')->updateOrInsert(
            ['username' => 'registrar'],
            [
                'name' => 'Registrar Demo User',
                'email' => 'registrar.demo@plp.local',
                'password' => Hash::make('registrar'),
                'module' => 'registrar',
                'student_id' => null,
                'faculty_id' => null,
                'registrar_id' => $registrar ? $registrar->id : null,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
    }
}
