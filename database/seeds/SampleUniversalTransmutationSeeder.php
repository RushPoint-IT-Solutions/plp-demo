<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SampleUniversalTransmutationSeeder extends Seeder
{
    public function run()
    {
        if (!Schema::hasTable('transmutation_rules')) {
            return;
        }

        $now = now();
        $bands = [
            ['from' => 98.00, 'to' => 100.00, 'grade' => 1.00, 'code' => 'A+', 'remarks' => 'Passed'],
            ['from' => 95.00, 'to' => 97.99, 'grade' => 1.25, 'code' => 'A', 'remarks' => 'Passed'],
            ['from' => 92.00, 'to' => 94.99, 'grade' => 1.50, 'code' => 'B+', 'remarks' => 'Passed'],
            ['from' => 89.00, 'to' => 91.99, 'grade' => 1.75, 'code' => 'B', 'remarks' => 'Passed'],
            ['from' => 86.00, 'to' => 88.99, 'grade' => 2.00, 'code' => 'C+', 'remarks' => 'Passed'],
            ['from' => 83.00, 'to' => 85.99, 'grade' => 2.25, 'code' => 'C', 'remarks' => 'Passed'],
            ['from' => 80.00, 'to' => 82.99, 'grade' => 2.50, 'code' => 'D+', 'remarks' => 'Passed'],
            ['from' => 77.00, 'to' => 79.99, 'grade' => 2.75, 'code' => 'D', 'remarks' => 'Passed'],
            ['from' => 75.00, 'to' => 76.99, 'grade' => 3.00, 'code' => 'P', 'remarks' => 'Passed'],
            ['from' => 0.00, 'to' => 74.99, 'grade' => 5.00, 'code' => 'F', 'remarks' => 'Failed'],
        ];

        $legacyGlobal = DB::table('transmutation_rules');

        foreach (['school_year', 'term', 'program', 'academic_term_id', 'course_id'] as $column) {
            if (Schema::hasColumn('transmutation_rules', $column)) {
                $legacyGlobal->whereNull($column);
            }
        }

        $legacyGlobal->delete();

        foreach ($bands as $band) {
            $identity = [
                'initial_from' => $band['from'],
                'initial_to' => $band['to'],
            ];

            $payload = [
                'transmuted_grade' => $band['grade'],
                'code' => $band['code'],
                'remarks' => $band['remarks'],
                'updated_at' => $now,
            ];

            if (Schema::hasColumn('transmutation_rules', 'school_year')) {
                $identity['school_year'] = null;
                $payload['school_year'] = null;
            }

            if (Schema::hasColumn('transmutation_rules', 'term')) {
                $identity['term'] = null;
                $payload['term'] = null;
            }

            if (Schema::hasColumn('transmutation_rules', 'program')) {
                $identity['program'] = null;
                $payload['program'] = null;
            }

            if (Schema::hasColumn('transmutation_rules', 'academic_term_id')) {
                $identity['academic_term_id'] = null;
                $payload['academic_term_id'] = null;
            }

            if (Schema::hasColumn('transmutation_rules', 'course_id')) {
                $identity['course_id'] = null;
                $payload['course_id'] = null;
            }

            DB::table('transmutation_rules')->updateOrInsert(
                $identity,
                array_merge($payload, ['created_at' => $now])
            );
        }
    }
}
