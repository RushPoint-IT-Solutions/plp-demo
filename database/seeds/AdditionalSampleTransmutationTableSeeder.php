<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdditionalSampleTransmutationTableSeeder extends Seeder
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

        $academicTerm = Schema::hasTable('academic_terms') && Schema::hasColumn('transmutation_rules', 'academic_term_id')
            ? DB::table('academic_terms')->orderByDesc('school_year')->orderBy('term')->first()
            : null;

        $course = Schema::hasTable('courses') && Schema::hasColumn('transmutation_rules', 'course_id')
            ? DB::table('courses')->orderBy('code')->first()
            : null;

        $schoolYear = $academicTerm ? (string) $academicTerm->school_year : '2026-2027';
        $term = $academicTerm ? (string) $academicTerm->term : 'First';
        $program = $course ? (string) $course->code : 'SAMPLE';

        foreach ($bands as $band) {
            $identity = [
                'initial_from' => $band['from'],
                'initial_to' => $band['to'],
            ];

            $payload = [
                'initial_from' => $band['from'],
                'initial_to' => $band['to'],
                'transmuted_grade' => $band['grade'],
                'code' => $band['code'],
                'remarks' => $band['remarks'],
                'updated_at' => $now,
            ];

            if (Schema::hasColumn('transmutation_rules', 'academic_term_id') && $academicTerm) {
                $identity['academic_term_id'] = (int) $academicTerm->id;
                $payload['academic_term_id'] = (int) $academicTerm->id;
            }

            if (Schema::hasColumn('transmutation_rules', 'course_id') && $course) {
                $identity['course_id'] = (int) $course->id;
                $payload['course_id'] = (int) $course->id;
            }

            if (Schema::hasColumn('transmutation_rules', 'school_year')) {
                $payload['school_year'] = $schoolYear;
                if (!$academicTerm || !Schema::hasColumn('transmutation_rules', 'academic_term_id')) {
                    $identity['school_year'] = $schoolYear;
                }
            }

            if (Schema::hasColumn('transmutation_rules', 'term')) {
                $payload['term'] = $term;
                if (!$academicTerm || !Schema::hasColumn('transmutation_rules', 'academic_term_id')) {
                    $identity['term'] = $term;
                }
            }

            if (Schema::hasColumn('transmutation_rules', 'program')) {
                $payload['program'] = $program;
                if (!$course || !Schema::hasColumn('transmutation_rules', 'course_id')) {
                    $identity['program'] = $program;
                }
            }

            DB::table('transmutation_rules')->updateOrInsert(
                $identity,
                array_merge($payload, ['created_at' => $now])
            );
        }
    }
}
