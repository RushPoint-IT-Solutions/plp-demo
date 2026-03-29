<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class YearBlockSeeder extends Seeder
{
    public function run()
    {
        $now = now();

        foreach (['1st Year', '2nd Year', '3rd Year', '4th Year'] as $label) {
            DB::table('year_blocks')->updateOrInsert(
                ['label' => $label],
                ['label' => $label, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }
}
