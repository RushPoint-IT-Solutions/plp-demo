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
        $hasSchoolYearColumn = Schema::hasColumn('transmutation_rules', 'school_year');
        $hasTermColumn = Schema::hasColumn('transmutation_rules', 'term');
        $hasProgramColumn = Schema::hasColumn('transmutation_rules', 'program');
        $hasAcademicTermIdColumn = Schema::hasColumn('transmutation_rules', 'academic_term_id');
        $hasCourseIdColumn = Schema::hasColumn('transmutation_rules', 'course_id');

        $courseIdsByCode = $hasCourseIdColumn
            ? DB::table('courses')->pluck('id', 'code')->toArray()
            : [];

        $academicTermIds = [];
        if ($hasAcademicTermIdColumn && Schema::hasTable('academic_terms')) {
            $academicTermIds = DB::table('academic_terms')
                ->select('id', 'school_year', 'term')
                ->get()
                ->mapWithKeys(function ($row) {
                    $canonicalTerm = $this->canonicalTermLabel($row->term);
                    $key = strtolower(trim((string) $row->school_year) . '|' . $canonicalTerm);

                    return [$key => (int) $row->id];
                })
                ->all();
        }

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
            $academicTermKey = strtolower(trim((string) $row['school_year']) . '|' . $this->canonicalTermLabel($row['term']));
            $academicTermId = isset($academicTermIds[$academicTermKey]) ? (int) $academicTermIds[$academicTermKey] : null;
            $courseId = isset($courseIdsByCode[$row['program']]) ? (int) $courseIdsByCode[$row['program']] : null;

            $insertPayload = [
                'initial_from' => $row['initial_from'],
                'initial_to' => $row['initial_to'],
                'transmuted_grade' => $row['transmuted_grade'],
                'code' => $row['code'],
                'remarks' => $row['remarks'],
            ];

            if ($hasSchoolYearColumn) {
                $insertPayload['school_year'] = $row['school_year'];
            }

            if ($hasTermColumn) {
                $insertPayload['term'] = $row['term'];
            }

            if ($hasProgramColumn) {
                $insertPayload['program'] = $row['program'];
            }

            if ($hasAcademicTermIdColumn) {
                $insertPayload['academic_term_id'] = $academicTermId;
            }

            if ($hasCourseIdColumn) {
                $insertPayload['course_id'] = $courseId;
            }

            $identity = [
                'initial_from' => $row['initial_from'],
                'initial_to' => $row['initial_to'],
            ];

            if ($hasAcademicTermIdColumn && $academicTermId) {
                $identity['academic_term_id'] = $academicTermId;
            } elseif ($hasSchoolYearColumn && $hasTermColumn) {
                $identity['school_year'] = $row['school_year'];
                $identity['term'] = $row['term'];
            }

            if ($hasCourseIdColumn && $courseId) {
                $identity['course_id'] = $courseId;
            } elseif ($hasProgramColumn) {
                $identity['program'] = $row['program'];
            }

            DB::table('transmutation_rules')->updateOrInsert(
                $identity,
                array_merge($insertPayload, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }
    }

    private function canonicalTermLabel($value)
    {
        $normalized = strtolower(trim((string) $value));

        if ($normalized === '') {
            return '';
        }

        if (strpos($normalized, 'summer') !== false) {
            return 'summer';
        }

        if (strpos($normalized, 'second') !== false || strpos($normalized, '2nd') !== false || $normalized === '2') {
            return 'second';
        }

        if (strpos($normalized, 'first') !== false || strpos($normalized, '1st') !== false || $normalized === '1') {
            return 'first';
        }

        return $normalized;
    }
}
