<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NormalizeGradingStructurePeriods extends Migration
{
    public function up()
    {
        if (Schema::hasTable('grade_rule_periods')) {
            DB::table('grade_rule_periods')
                ->where('period_name', 'Finals')
                ->update(['period_name' => 'Final']);

            DB::table('grade_rule_periods')
                ->where('period_name', 'Pre-Final')
                ->delete();
        }

        if (Schema::hasTable('grade_rules') && Schema::hasColumn('grade_rules', 'periods')) {
            $rows = DB::table('grade_rules')->select('id', 'periods')->get();

            foreach ($rows as $row) {
                $periods = json_decode((string) $row->periods, true);
                if (!is_array($periods)) {
                    continue;
                }

                $cleanPeriods = collect($periods)
                    ->map(function ($period) {
                        return trim((string) $period) === 'Finals' ? 'Final' : trim((string) $period);
                    })
                    ->filter(function ($period) {
                        return in_array($period, ['Prelim', 'Midterm', 'Final'], true);
                    })
                    ->unique()
                    ->values()
                    ->all();

                DB::table('grade_rules')
                    ->where('id', $row->id)
                    ->update([
                        'periods' => json_encode($cleanPeriods),
                        'updated_at' => now(),
                    ]);
            }
        }

        foreach (['grading_periods', 'grading_components', 'system_grade_postings'] as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'period')) {
                continue;
            }

            DB::table($table)
                ->where('period', 'Finals')
                ->update(['period' => 'Final']);

            DB::table($table)
                ->where('period', 'Pre-Final')
                ->delete();
        }
    }

    public function down()
    {
        // The old Pre-Final rows cannot be reconstructed safely.
    }
}
