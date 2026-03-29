<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TransmutationRuleSeeder extends Seeder
{
    public function run()
    {
        if (!Schema::hasTable('transmutation_rules')) {
            return;
        }

        $now = now();
        $rows = [
            [
                'school_year' => '2025-2026',
                'term' => 'First',
                'program' => 'BSIT',
                'initial_from' => 75.00,
                'initial_to' => 79.99,
                'transmuted_grade' => 3.00,
                'code' => 'P',
                'remarks' => 'Passed',
            ],
            [
                'school_year' => '2025-2026',
                'term' => 'First',
                'program' => 'BSIT',
                'initial_from' => 80.00,
                'initial_to' => 84.99,
                'transmuted_grade' => 2.50,
                'code' => 'P',
                'remarks' => 'Passed',
            ],
            [
                'school_year' => '2025-2026',
                'term' => 'First',
                'program' => 'BSIT',
                'initial_from' => 0.00,
                'initial_to' => 74.99,
                'transmuted_grade' => 5.00,
                'code' => 'F',
                'remarks' => 'Failed',
            ],
        ];

        foreach ($rows as $row) {
            DB::table('transmutation_rules')->updateOrInsert(
                [
                    'school_year' => $row['school_year'],
                    'term' => $row['term'],
                    'program' => $row['program'],
                    'initial_from' => $row['initial_from'],
                    'initial_to' => $row['initial_to'],
                ],
                array_merge($row, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }
    }
}
