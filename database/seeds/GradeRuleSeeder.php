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
            ['code' => 'OD', 'grade' => 'OD', 'remarks' => 'Officially Drop'],
            ['code' => 'UD', 'grade' => 'UD', 'remarks' => 'Unofficially Drop'],
            ['code' => 'INC', 'grade' => 'INC', 'remarks' => 'Incomplete'],
            ['code' => 'F', 'grade' => 'F', 'remarks' => 'Failed'],
            ['code' => 'P', 'grade' => 'P', 'remarks' => 'Pass'],
        ];
        $defaultPeriods = ['Midterm', 'Final'];

        foreach ($rows as $row) {
            $payload = [
                'grade' => $row['grade'],
                'remarks' => $row['remarks'],
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (!Schema::hasTable('grade_rule_periods') && Schema::hasColumn('grade_rules', 'periods')) {
                $payload['periods'] = json_encode($defaultPeriods);
            }

            DB::table('grade_rules')->updateOrInsert(
                ['code' => $row['code']],
                $payload
            );

            if (!Schema::hasTable('grade_rule_periods')) {
                continue;
            }

            $ruleId = DB::table('grade_rules')->where('code', $row['code'])->value('id');
            if (empty($ruleId)) {
                continue;
            }

            DB::table('grade_rule_periods')->where('grade_rule_id', $ruleId)->delete();

            foreach ($defaultPeriods as $index => $periodName) {
                DB::table('grade_rule_periods')->insert([
                    'grade_rule_id' => $ruleId,
                    'period_name' => $periodName,
                    'sort_order' => $index + 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
