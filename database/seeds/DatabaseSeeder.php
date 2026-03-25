<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(StudentSeeder::class);
        $this->call(SubjectSeeder::class);
        $this->call(FacultySeeder::class);
        $this->call(ApplicantSeeder::class);
        $this->call(StudentDemoDataSeeder::class);
        $this->call(FacultyAuthSeeder::class);
        $this->call(RegistrarAuthSeeder::class);
    }
}
