<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Corrects the global transmutation bands (course_id and academic_term_id
 * both NULL) to match the school's real equivalent-grade scale, printed
 * on the official Report of Grade legend. The rows previously used
 * boundaries shifted up to 1.5 points off (e.g. 95.00-97.99 => 1.25
 * instead of the real 94.50-97.49 => 1.25), so a raw average near a
 * band edge transmuted to the wrong grade point.
 *
 * Safe to run on live: matches existing rows by `code`, only updates
 * initial_from/initial_to, and does nothing if a code is missing.
 */
class FixDefaultTransmutationBandsSeeder extends Seeder
{
    public function run()
    {
        $corrections = [
            'A+' => ['from' => 97.50, 'to' => 100.00],
            'A'  => ['from' => 94.50, 'to' => 97.49],
            'B+' => ['from' => 91.50, 'to' => 94.49],
            'B'  => ['from' => 88.50, 'to' => 91.49],
            'C+' => ['from' => 85.50, 'to' => 88.49],
            'C'  => ['from' => 82.50, 'to' => 85.49],
            'D+' => ['from' => 79.50, 'to' => 82.49],
            'D'  => ['from' => 76.50, 'to' => 79.49],
            'P'  => ['from' => 74.50, 'to' => 76.49],
            'F'  => ['from' => 0.00, 'to' => 74.49],
        ];

        $updated = 0;

        foreach ($corrections as $code => $bounds) {
            $affected = DB::table('transmutation_rules')
                ->whereNull('course_id')
                ->whereNull('academic_term_id')
                ->where('code', $code)
                ->update([
                    'initial_from' => $bounds['from'],
                    'initial_to' => $bounds['to'],
                    'updated_at' => now(),
                ]);

            $updated += $affected;
        }

        $this->command->info("Corrected {$updated} global transmutation_rules row(s).");
    }
}
