<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SemesterSeeder extends Seeder
{
    public function run()
    {
        $now = now();

        foreach (['1st Semester', '2nd Semester', 'Summer'] as $name) {
            DB::table('semesters')->updateOrInsert(
                ['name' => $name],
                ['name' => $name, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }
}
