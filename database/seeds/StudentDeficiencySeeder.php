<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StudentDeficiencySeeder extends Seeder
{
    public function run()
    {
        if (!Schema::hasTable('student_deficiencies') || !Schema::hasTable('students')) {
            return;
        }

        $student = DB::table('students')->orderBy('id')->first();
        if (!$student) {
            return;
        }

        $now = now();

        DB::table('student_deficiencies')->updateOrInsert(
            [
                'student_id' => $student->id,
                'department' => 'Library',
                'remarks' => 'Damaged Item (Math book)',
            ],
            [
                'date_today' => '2026-03-01',
                'submission_date' => '2026-03-10',
                'is_completed' => false,
                'compliance_date' => null,
                'updated_by' => 'Admin 1',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('student_deficiencies')->updateOrInsert(
            [
                'student_id' => $student->id,
                'department' => 'Cashier',
                'remarks' => 'Unsettled fee balance',
            ],
            [
                'date_today' => '2026-03-01',
                'submission_date' => '2026-03-15',
                'is_completed' => false,
                'compliance_date' => null,
                'updated_by' => 'Admin 1',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}
