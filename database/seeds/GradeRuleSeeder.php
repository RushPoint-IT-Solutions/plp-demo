<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GradeRuleSeeder extends Seeder
{
    public function run()
    {
        if (!Schema::hasTable('grade_rules')) {
            return;
        }

        $now = now();
        $rows = [
            ['code' => 'INC', 'grade' => 'N/A', 'remarks' => 'No Appearance'],
            ['code' => 'FDA', 'grade' => 'FDA', 'remarks' => 'Failure Due To Absences'],
            ['code' => 'P', 'grade' => '1.0-3.0', 'remarks' => 'Passed'],
            ['code' => 'F', 'grade' => '5.0', 'remarks' => 'Failed'],
        ];

        foreach ($rows as $row) {
            DB::table('grade_rules')->updateOrInsert(
                ['code' => $row['code']],
                [
                    'grade' => $row['grade'],
                    'remarks' => $row['remarks'],
                    'periods' => json_encode(['Prelim', 'Midterm', 'Pre-Final', 'Finals']),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
