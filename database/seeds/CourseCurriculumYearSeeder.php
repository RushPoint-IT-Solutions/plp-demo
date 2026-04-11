<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CourseCurriculumYearSeeder extends Seeder
{
    public function run()
    {
        if (app()->environment('production')) {
            throw new \Exception('Cannot seed in production.');
        }

        if (!Schema::hasTable('course_curricula')) {
            $this->command->warn('CourseCurriculumYearSeeder: course_curricula table is missing; skipping.');
            return;
        }

        $target = (int) env('SEED_COURSE_CURRICULA_TARGET', 1000);
        if ($target < 1000) {
            $target = 1000;
        }

        $yearCodes = $this->resolveYearCodes($target);
        if (!count($yearCodes)) {
            $this->command->warn('CourseCurriculumYearSeeder: no curriculum year codes available; skipping.');
            return;
        }

        $this->ensureCourseCapacity($target, count($yearCodes));

        $courses = DB::table('courses')
            ->orderBy('id')
            ->get(['id', 'code', 'name', 'description']);

        if ($courses->isEmpty()) {
            $this->command->warn('CourseCurriculumYearSeeder: no courses available; skipping.');
            return;
        }

        $hasCurriculumYearLookup = Schema::hasTable('curriculum_years');
        $hasCurriculumYearIdColumn = Schema::hasTable('course_curricula')
            && Schema::hasColumn('course_curricula', 'curriculum_year_id');

        $yearIdMap = $hasCurriculumYearLookup
            ? $this->seedCurriculumYearLookup($yearCodes)
            : [];

        $existingPairs = [];
        DB::table('course_curricula')
            ->select(['course_id', 'curriculum_year_code'])
            ->orderBy('id')
            ->chunk(500, function ($rows) use (&$existingPairs) {
                foreach ($rows as $row) {
                    $pairKey = (int) $row->course_id . ':' . trim((string) $row->curriculum_year_code);
                    $existingPairs[$pairKey] = true;
                }
            });

        $rows = [];
        $created = 0;
        $existing = 0;
        $totalExpectedPairs = count($yearCodes) * (int) $courses->count();

        foreach ($courses as $course) {
            foreach ($yearCodes as $yearCode) {
                $pairKey = (int) $course->id . ':' . $yearCode;

                if (isset($existingPairs[$pairKey])) {
                    $existing++;
                    continue;
                }

                $courseLabel = trim((string) ($course->code ?: $course->name ?: $course->description));
                if ($courseLabel === '') {
                    $courseLabel = 'COURSE-' . (int) $course->id;
                }

                $row = [
                    'course_id' => (int) $course->id,
                    'curriculum_year_code' => $yearCode,
                    'title' => $courseLabel . ' Curriculum ' . $yearCode,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if ($hasCurriculumYearLookup && $hasCurriculumYearIdColumn) {
                    $row['curriculum_year_id'] = $yearIdMap[$yearCode] ?? null;
                }

                $rows[] = $row;
                $existingPairs[$pairKey] = true;
                $created++;

                if (count($rows) >= 400) {
                    DB::table('course_curricula')->insert($rows);
                    $rows = [];
                }
            }
        }

        if (count($rows)) {
            DB::table('course_curricula')->insert($rows);
        }

        if ($hasCurriculumYearLookup && $hasCurriculumYearIdColumn) {
            $this->syncCurriculumYearIds();
        }

        $finalCount = (int) DB::table('course_curricula')->count();
        $this->command->info(
            'CourseCurriculumYearSeeder: ensured full matrix with '
            . $totalExpectedPairs
            . ' expected pair(s); inserted '
            . $created
            . ' missing pair(s), retained '
            . $existing
            . ' existing pair(s); total course_curricula = '
            . $finalCount
            . '.'
        );
    }

    private function resolveYearCodes($target)
    {
        $yearCodes = [];

        if (Schema::hasTable('curriculum_years')) {
            $yearCodes = DB::table('curriculum_years')
                ->whereNotNull('code')
                ->where('code', '<>', '')
                ->orderBy('code')
                ->pluck('code')
                ->map(function ($code) {
                    return trim((string) $code);
                })
                ->filter(function ($code) {
                    return $code !== '';
                })
                ->values()
                ->all();
        }

        if (!count($yearCodes)) {
            $yearCodes = DB::table('course_curricula')
                ->whereNotNull('curriculum_year_code')
                ->where('curriculum_year_code', '<>', '')
                ->orderBy('curriculum_year_code')
                ->distinct()
                ->pluck('curriculum_year_code')
                ->map(function ($code) {
                    return trim((string) $code);
                })
                ->filter(function ($code) {
                    return $code !== '';
                })
                ->values()
                ->all();
        }

        if (!count($yearCodes)) {
            $yearCodes = $this->buildYearCodes($this->minimumGeneratedYearCount($target), 1980);
        }

        return collect($yearCodes)
            ->map(function ($code) {
                return trim((string) $code);
            })
            ->filter(function ($code) {
                return $code !== '';
            })
            ->unique()
            ->values()
            ->all();
    }

    private function buildYearCodes($count, $startYear)
    {
        $codes = [];
        $year = (int) $startYear;

        for ($i = 0; $i < (int) $count; $i++) {
            $codes[] = $year . '-' . ($year + 1);
            $year++;
        }

        return $codes;
    }

    private function minimumGeneratedYearCount($target)
    {
        $courseCount = Schema::hasTable('courses') ? (int) DB::table('courses')->count() : 0;
        $safeCourseCount = max($courseCount, 1);

        return max((int) ceil(((int) $target) / $safeCourseCount), 1);
    }

    private function seedCurriculumYearLookup(array $yearCodes)
    {
        $now = now();

        foreach ($yearCodes as $code) {
            DB::table('curriculum_years')->updateOrInsert(
                ['code' => $code],
                [
                    'label' => 'AY ' . $code,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        return DB::table('curriculum_years')
            ->whereIn('code', $yearCodes)
            ->pluck('id', 'code')
            ->map(function ($id) {
                return (int) $id;
            })
            ->all();
    }

    private function syncCurriculumYearIds()
    {
        DB::statement(
            'UPDATE course_curricula cc
            INNER JOIN curriculum_years cy ON cy.code = cc.curriculum_year_code
            SET cc.curriculum_year_id = cy.id
            WHERE cc.curriculum_year_id IS NULL'
        );
    }

    private function ensureCourseCapacity($target, $yearCodeCount)
    {
        if (!Schema::hasTable('courses')) {
            return;
        }

        $safeYearCodeCount = max((int) $yearCodeCount, 1);
        $minimumCourses = (int) ceil(((int) $target) / $safeYearCodeCount);
        $currentCourses = (int) DB::table('courses')->count();

        if ($currentCourses >= $minimumCourses) {
            return;
        }

        $departmentId = DB::table('departments')->orderBy('id')->value('id');
        if (!$departmentId) {
            $departmentId = DB::table('departments')->insertGetId([
                'code' => 'AUTO',
                'description' => 'Auto-generated Department',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $needed = $minimumCourses - $currentCourses;
        $this->seedAdditionalCourses((int) $departmentId, $needed);
    }

    private function seedAdditionalCourses($departmentId, $count)
    {
        $now = now();

        for ($i = 1; $i <= (int) $count; $i++) {
            $code = $this->nextGeneratedCourseCode();

            DB::table('courses')->insert([
                'code' => $code,
                'name' => 'Auto Course ' . $code,
                'program_type' => 'college',
                'department_id' => (int) $departmentId,
                'description' => 'Auto-generated course for curriculum load testing.',
                'slots' => 100,
                'track_category' => null,
                'non_filipino' => false,
                'dean_director_id' => null,
                'program_file' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function nextGeneratedCourseCode()
    {
        $sequence = (int) DB::table('courses')
            ->where('code', 'like', 'AUTOCRS%')
            ->count();

        do {
            $sequence++;
            $candidate = 'AUTOCRS' . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
        } while (DB::table('courses')->where('code', $candidate)->exists());

        return $candidate;
    }
}
