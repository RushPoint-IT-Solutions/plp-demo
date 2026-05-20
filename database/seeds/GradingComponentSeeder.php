<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GradingComponentSeeder extends Seeder
{
    public function run()
    {
        if (!Schema::hasTable('grading_components')) {
            return;
        }

        $now = now();
        $hasSchoolYearColumn = Schema::hasColumn('grading_components', 'school_year');
        $hasSemesterColumn = Schema::hasColumn('grading_components', 'semester');
        $hasAcademicTermIdColumn = Schema::hasColumn('grading_components', 'academic_term_id');

        $academicTermIds = [];
        if ($hasAcademicTermIdColumn && Schema::hasTable('academic_terms')) {
            $academicTermIds = DB::table('academic_terms')
                ->select('id', 'school_year', 'term')
                ->get()
                ->mapWithKeys(function ($row) {
                    $key = strtolower(trim((string) $row->school_year) . '|' . trim((string) $row->term));

                    return [$key => (int) $row->id];
                })
                ->all();
        }

        $rows = [
            [
                'school_year' => '2025-2026',
                'period' => 'Midterm',
                'semester' => 'First',
                'section' => 'BSIT-4A',
                'course_code' => 'IT 4102',
                'title' => 'Written/Seatwork',
                'sequence_no' => 1,
                'percentage' => 30,
                'lab_mode' => 'No',
                'cap' => 'No',
                'updated_by' => 'Registrar',
                'effective_date' => '2026-03-07',
            ],
            [
                'school_year' => '2025-2026',
                'period' => 'Final',
                'semester' => 'First',
                'section' => 'BSCS-3A',
                'course_code' => 'CS 301',
                'title' => 'Quiz',
                'sequence_no' => 2,
                'percentage' => 30,
                'lab_mode' => 'No',
                'cap' => 'No',
                'updated_by' => 'Registrar',
                'effective_date' => '2026-03-07',
            ],
        ];

        foreach ($rows as $row) {
            $academicTermKey = strtolower(trim((string) $row['school_year']) . '|' . trim((string) $row['semester']));
            $academicTermId = isset($academicTermIds[$academicTermKey]) ? (int) $academicTermIds[$academicTermKey] : null;

            $payload = [
                'period' => $row['period'],
                'section' => $row['section'],
                'course_code' => $row['course_code'],
                'title' => $row['title'],
                'sequence_no' => $row['sequence_no'],
                'percentage' => $row['percentage'],
                'lab_mode' => $row['lab_mode'],
                'cap' => $row['cap'],
                'updated_by' => $row['updated_by'],
                'effective_date' => $row['effective_date'],
            ];

            $identity = [
                'period' => $row['period'],
                'section' => $row['section'],
                'course_code' => $row['course_code'],
                'title' => $row['title'],
                'sequence_no' => $row['sequence_no'],
            ];

            if ($hasSchoolYearColumn) {
                $payload['school_year'] = $row['school_year'];
                $identity['school_year'] = $row['school_year'];
            }

            if ($hasSemesterColumn) {
                $payload['semester'] = $row['semester'];
                $identity['semester'] = $row['semester'];
            }

            if ($hasAcademicTermIdColumn) {
                $payload['academic_term_id'] = $academicTermId;

                if ($academicTermId) {
                    $identity['academic_term_id'] = $academicTermId;
                }
            }

            DB::table('grading_components')->updateOrInsert(
                $identity,
                array_merge($payload, ['created_at' => $now, 'updated_at' => $now])
            );
        }
    }
}
