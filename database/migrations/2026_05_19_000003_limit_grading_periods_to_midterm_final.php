<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LimitGradingPeriodsToMidtermFinal extends Migration
{
    public function up()
    {
        if (Schema::hasTable('grade_rule_periods')) {
            DB::table('grade_rule_periods')->whereNotIn('period_name', ['Midterm', 'Final'])->delete();
        }

        if (Schema::hasTable('grade_rules') && Schema::hasColumn('grade_rules', 'periods')) {
            DB::table('grade_rules')->update([
                'periods' => json_encode(['Midterm', 'Final']),
                'updated_at' => now(),
            ]);
        }

        foreach (['grading_periods', 'grading_components', 'system_grade_postings'] as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'period')) {
                continue;
            }

            DB::table($table)->whereNotIn('period', ['Midterm', 'Final'])->delete();
        }
    }

    public function down()
    {
        // Prelim rows were intentionally removed to enforce the two-period grading format.
    }
}
