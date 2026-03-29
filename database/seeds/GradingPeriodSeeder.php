<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GradingPeriodSeeder extends Seeder
{
    public function run()
    {
        if (!Schema::hasTable('grading_periods')) {
            return;
        }

        $now = now();
        $rows = [
            [
                'school_year' => '2025-2026',
                'semester' => 'First',
                'section_subject_faculty' => 'BSCS 3A / CS301 / MARASIGAN',
                'period' => 'Prelim',
                'description' => 'Written/Seatwork',
                'percentage' => 30,
                'start_date' => '2026-03-01',
                'end_date' => '2026-03-30',
                'start_time' => '08:00',
                'grading_computation' => 'Weighted',
                'use_grades_library' => true,
            ],
            [
                'school_year' => '2025-2026',
                'semester' => 'First',
                'section_subject_faculty' => 'BSIT 2B / IT201 / DELA CRUZ',
                'period' => 'Midterm',
                'description' => 'Project',
                'percentage' => 40,
                'start_date' => '2026-04-01',
                'end_date' => '2026-04-30',
                'start_time' => '10:00',
                'grading_computation' => 'Weighted',
                'use_grades_library' => true,
            ],
        ];

        foreach ($rows as $row) {
            DB::table('grading_periods')->updateOrInsert(
                [
                    'school_year' => $row['school_year'],
                    'semester' => $row['semester'],
                    'section_subject_faculty' => $row['section_subject_faculty'],
                    'period' => $row['period'],
                    'description' => $row['description'],
                ],
                array_merge($row, ['created_at' => $now, 'updated_at' => $now])
            );
        }
    }
}
