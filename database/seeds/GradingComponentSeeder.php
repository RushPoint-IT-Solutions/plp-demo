<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GradingComponentSeeder extends Seeder
{
    public function run()
    {
        if (!Schema::hasTable('grading_components')) {
            return;
        }

        $now = now();
        $rows = [
            [
                'school_year' => '2025',
                'period' => 'Prelim',
                'semester' => 'First',
                'section' => 'BSIT-4A',
                'course_code' => 'IT 4102',
                'title' => 'Written/Seatwork',
                'sequence_no' => 1,
                'percentage' => 30,
                'lab_mode' => 'No',
                'cap' => 'No',
                'updated_by' => 'Registrar',
                'effective_date' => '2026-03-07',
            ],
            [
                'school_year' => '2025',
                'period' => 'Prelim',
                'semester' => 'First',
                'section' => 'BSCS-3A',
                'course_code' => 'CS 301',
                'title' => 'Quiz',
                'sequence_no' => 2,
                'percentage' => 30,
                'lab_mode' => 'No',
                'cap' => 'No',
                'updated_by' => 'Registrar',
                'effective_date' => '2026-03-07',
            ],
        ];

        foreach ($rows as $row) {
            DB::table('grading_components')->updateOrInsert(
                [
                    'school_year' => $row['school_year'],
                    'period' => $row['period'],
                    'semester' => $row['semester'],
                    'section' => $row['section'],
                    'course_code' => $row['course_code'],
                    'title' => $row['title'],
                    'sequence_no' => $row['sequence_no'],
                ],
                array_merge($row, ['created_at' => $now, 'updated_at' => $now])
            );
        }
    }
}
