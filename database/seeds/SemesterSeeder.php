<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SemesterSeeder extends Seeder
{
    public function run()
    {
        $now = now();

        foreach (['First Semester', 'Second Semester', 'Summer Semester'] as $name) {
            DB::table('semesters')->updateOrInsert(
                ['name' => $name],
                ['name' => $name, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        // Earlier seed data used different naming ("1st Semester", "Summer", etc.)
        // that doesn't match the convention RegistrarController::orderedSemesters()
        // and the rest of the app expect. Safe to remove: no table with a
        // semester_id column references these rows.
        DB::table('semesters')
            ->whereNotIn('name', ['First Semester', 'Second Semester', 'Summer Semester'])
            ->delete();
    }
}
