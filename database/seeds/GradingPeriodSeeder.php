<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GradingPeriodSeeder extends Seeder
{
    public function run()
    {
        if (!Schema::hasTable('grading_periods')) {
            return;
        }

        $now = now();
        $hasSchoolYearColumn = Schema::hasColumn('grading_periods', 'school_year');
        $hasSemesterColumn = Schema::hasColumn('grading_periods', 'semester');
        $hasAcademicTermIdColumn = Schema::hasColumn('grading_periods', 'academic_term_id');

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
                'semester' => 'First',
                'section_subject_faculty' => 'BSCS 3A / CS301 / MARASIGAN',
                'period' => 'Prelim',
                'description' => 'Written/Seatwork',
                'percentage' => 30,
                'start_date' => '2026-03-01',
                'end_date' => '2026-03-30',
                'start_time' => '08:00',
                'grading_computation' => 'Weighted',
                'use_grades_library' => true,
            ],
            [
                'school_year' => '2025-2026',
                'semester' => 'First',
                'section_subject_faculty' => 'BSIT 2B / IT201 / DELA CRUZ',
                'period' => 'Midterm',
                'description' => 'Project',
                'percentage' => 40,
                'start_date' => '2026-04-01',
                'end_date' => '2026-04-30',
                'start_time' => '10:00',
                'grading_computation' => 'Weighted',
                'use_grades_library' => true,
            ],
        ];

        foreach ($rows as $row) {
            $academicTermKey = strtolower(trim((string) $row['school_year']) . '|' . trim((string) $row['semester']));
            $academicTermId = isset($academicTermIds[$academicTermKey]) ? (int) $academicTermIds[$academicTermKey] : null;

            $payload = [
                'section_subject_faculty' => $row['section_subject_faculty'],
                'period' => $row['period'],
                'description' => $row['description'],
                'percentage' => $row['percentage'],
                'start_date' => $row['start_date'],
                'end_date' => $row['end_date'],
                'start_time' => $row['start_time'],
                'grading_computation' => $row['grading_computation'],
                'use_grades_library' => $row['use_grades_library'],
            ];

            $identity = [
                'section_subject_faculty' => $row['section_subject_faculty'],
                'period' => $row['period'],
                'description' => $row['description'],
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

            DB::table('grading_periods')->updateOrInsert(
                $identity,
                array_merge($payload, ['created_at' => $now, 'updated_at' => $now])
            );
        }
    }
}
