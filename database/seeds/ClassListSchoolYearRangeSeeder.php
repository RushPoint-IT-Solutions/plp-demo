<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClassListSchoolYearRangeSeeder extends Seeder
{
    public function run()
    {
        if (!Schema::hasTable('academic_terms')) {
            $this->command->warn('ClassListSchoolYearRangeSeeder: academic_terms table not found; skipping.');
            return;
        }

        $startYear = (int) env('CLASS_LIST_SCHOOL_YEAR_START', 2000);
        $endYear = (int) env('CLASS_LIST_SCHOOL_YEAR_END', 2026);

        if ($endYear < $startYear) {
            $swap = $startYear;
            $startYear = $endYear;
            $endYear = $swap;
        }

        $termDefinitions = [
            'First' => 'First Semester',
            'Second' => 'Second Semester',
            'Summer' => 'Summer',
        ];

        $createdTerms = 0;
        $updatedTerms = 0;
        $termRows = [];

        for ($year = $startYear; $year <= $endYear; $year++) {
            $schoolYear = $year . '-' . ($year + 1);

            foreach ($termDefinitions as $normalized => $label) {
                $canonicalKey = strtolower($schoolYear . '|' . $label);

                $existing = DB::table('academic_terms')
                    ->where('canonical_key', $canonicalKey)
                    ->first(['id', 'school_year', 'term']);

                if ($existing) {
                    DB::table('academic_terms')
                        ->where('id', (int) $existing->id)
                        ->update([
                            'school_year' => $schoolYear,
                            'term' => $label,
                            'updated_at' => now(),
                        ]);

                    $termId = (int) $existing->id;
                    $updatedTerms++;
                } else {
                    $termId = (int) DB::table('academic_terms')->insertGetId([
                        'school_year' => $schoolYear,
                        'term' => $label,
                        'canonical_key' => $canonicalKey,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $createdTerms++;
                }

                $termRows[] = [
                    'id' => $termId,
                    'school_year' => $schoolYear,
                    'term' => $label,
                    'normalized' => $normalized,
                ];
            }
        }

        $syncedSystemRows = $this->syncSystemSchoolSemesters($termRows);

        $this->command->info(
            'ClassListSchoolYearRangeSeeder: seeded school years '
            . $startYear
            . ' to '
            . $endYear
            . ' (created terms: '
            . $createdTerms
            . ', updated terms: '
            . $updatedTerms
            . ', synced system rows: '
            . $syncedSystemRows
            . ').'
        );
    }

    private function syncSystemSchoolSemesters(array $termRows)
    {
        if (!Schema::hasTable('system_school_semesters') || !count($termRows)) {
            return 0;
        }

        $hasAcademicTermId = Schema::hasColumn('system_school_semesters', 'academic_term_id');
        $hasSchoolYear = Schema::hasColumn('system_school_semesters', 'school_year');
        $hasSemester = Schema::hasColumn('system_school_semesters', 'semester');
        $hasCreatedAt = Schema::hasColumn('system_school_semesters', 'created_at');
        $hasUpdatedAt = Schema::hasColumn('system_school_semesters', 'updated_at');

        if (!$hasAcademicTermId && !$hasSchoolYear && !$hasSemester) {
            return 0;
        }

        $updated = 0;

        foreach ($termRows as $termRow) {
            $termId = (int) $termRow['id'];
            $schoolYear = (string) $termRow['school_year'];
            $semester = (string) $termRow['term'];

            $query = DB::table('system_school_semesters');
            if ($hasAcademicTermId) {
                $query->where('academic_term_id', $termId);
            } elseif ($hasSchoolYear && $hasSemester) {
                $query->where('school_year', $schoolYear)->where('semester', $semester);
            }

            $existing = $query->first(['id']);

            $payload = [];
            if ($hasAcademicTermId) {
                $payload['academic_term_id'] = $termId;
            }
            if ($hasSchoolYear) {
                $payload['school_year'] = $schoolYear;
            }
            if ($hasSemester) {
                $payload['semester'] = $semester;
            }
            if ($hasUpdatedAt) {
                $payload['updated_at'] = now();
            }

            if ($existing) {
                DB::table('system_school_semesters')
                    ->where('id', (int) $existing->id)
                    ->update($payload);

                $updated++;
                continue;
            }

            if ($hasCreatedAt) {
                $payload['created_at'] = now();
            }

            DB::table('system_school_semesters')->insert($payload);
            $updated++;
        }

        return $updated;
    }
}
