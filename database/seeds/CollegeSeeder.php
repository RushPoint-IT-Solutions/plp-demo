<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CollegeSeeder extends Seeder
{
    /**
     * Seed the PLP colleges lookup table.
     *
     * Run with: php artisan db:seed --class=CollegeSeeder
     *
     * @return void
     */
    public function run()
    {
        if (!Schema::hasTable('colleges')) {
            $this->command->warn('colleges table does not exist. Run migrations first.');
            return;
        }

        $now = now();

        $colleges = [
            [
                'code'       => 'CON',
                'name'       => 'COLLEGE OF NURSING',
                'abbr'       => 'CON',
                'sort_order' => 1,
            ],
            [
                'code'       => 'CAS',
                'name'       => 'COLLEGE OF ARTS AND SCIENCES',
                'abbr'       => 'CAS',
                'sort_order' => 2,
            ],
            [
                'code'       => 'CCS',
                'name'       => 'COLLEGE OF COMPUTER STUDIES',
                'abbr'       => 'CCS',
                'sort_order' => 3,
            ],
            [
                'code'       => 'COE',
                'name'       => 'COLLEGE OF ENGINEERING',
                'abbr'       => 'COE',
                'sort_order' => 4,
            ],
            [
                'code'       => 'CIHM',
                'name'       => 'COLLEGE OF INTERNATIONAL HOSPITALITY MANAGEMENT',
                'abbr'       => 'CIHM',
                'sort_order' => 5,
            ],
            [
                'code'       => 'CBA',
                'name'       => 'COLLEGE OF BUSINESS AND ACCOUNTANCY',
                'abbr'       => 'CBA',
                'sort_order' => 6,
            ],
            [
                'code'       => 'COED',
                'name'       => 'COLLEGE OF EDUCATION',
                'abbr'       => 'COED',
                'sort_order' => 7,
            ],
        ];

        foreach ($colleges as $college) {
            DB::table('colleges')->updateOrInsert(
                ['code' => $college['code']],
                array_merge($college, [
                    'updated_at' => $now,
                    'created_at' => $now,
                ])
            );
        }

        $this->command->info('7 PLP colleges seeded successfully.');
    }
}
