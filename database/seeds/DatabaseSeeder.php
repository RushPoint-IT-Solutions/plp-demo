<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Registrar;
use App\Faculty;
use App\Department;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 1. Create a Root Department
        $department = Department::create([
            'code' => 'CS',
            'description' => 'College of Computer Studies',
        ]);

        // 2. Create a Root Faculty
        $faculty = Faculty::create([
            'code' => 'FAC-001',
            'name' => 'John Doe',
        ]);

        // 3. Create a Root Registrar
        $registrar = Registrar::create([
            'code' => 'REG-001',
            'name' => 'System Registrar',
            'email' => 'registrar@plp.edu.ph',
        ]);

        // 4. Create the Root User Account
        User::create([
            'name' => 'System Registrar',
            'username' => 'admin',
            'email' => 'admin@plp.edu.ph',
            'password' => Hash::make('password'),
            'module' => 'registrar',
            'force_password_reset' => false,
            'registrar_id' => $registrar->id,
        ]);
        
        $this->command->info('Database seeded successfully with a root registrar user! Username: admin | Password: password');
    }
}
