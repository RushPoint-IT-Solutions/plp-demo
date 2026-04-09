<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CourseCurriculumSubjectMatrixSeeder extends Seeder
{
    public function run()
    {
        if (app()->environment('production')) {
            throw new \Exception('Cannot seed in production.');
        }

        if (!$this->hasRequiredTables()) {
            $this->command->warn('CourseCurriculumSubjectMatrixSeeder: required tables are missing; skipping.');
            return;
        }

        $subjectsPerCurriculum = (int) env('SEED_CURRICULUM_SUBJECTS_PER_CURRICULUM', 12);
        $subjectsPerCurriculum = max(1, min($subjectsPerCurriculum, 24));

        $yearBlockIds = $this->ensureYearBlocks();
        $semesterIds = $this->ensureSemesters();
        $slotPlan = $this->buildSlotPlan($yearBlockIds, $semesterIds);

        if (!count($slotPlan)) {
            $this->command->warn('CourseCurriculumSubjectMatrixSeeder: no year/semester slots available; skipping.');
            return;
        }

        $subjects = DB::table('subjects')
            ->select(['id', 'course_id', 'units'])
            ->orderBy('id')
            ->get();

        if ($subjects->isEmpty()) {
            $this->command->warn('CourseCurriculumSubjectMatrixSeeder: no subjects available; skipping.');
            return;
        }

        $globalSubjectIds = $subjects->pluck('id')->map(function ($id) {
            return (int) $id;
        })->values()->all();

        $subjectUnits = [];
        $courseSubjectMap = [];

        foreach ($subjects as $subject) {
            $subjectId = (int) $subject->id;
            $subjectUnits[$subjectId] = is_null($subject->units) ? 3.0 : (float) $subject->units;

            if (!is_null($subject->course_id)) {
                $courseId = (int) $subject->course_id;
                if (!isset($courseSubjectMap[$courseId])) {
                    $courseSubjectMap[$courseId] = [];
                }

                $courseSubjectMap[$courseId][] = $subjectId;
            }
        }

        $createdRows = 0;
        $processedCurricula = 0;

        DB::table('course_curricula')
            ->select(['id', 'course_id'])
            ->orderBy('id')
            ->chunk(200, function ($curricula) use (
                $subjectsPerCurriculum,
                $slotPlan,
                $courseSubjectMap,
                $globalSubjectIds,
                $subjectUnits,
                &$createdRows,
                &$processedCurricula
            ) {
                foreach ($curricula as $curriculum) {
                    $processedCurricula++;
                    $createdRows += $this->seedCurriculumAssignments(
                        (int) $curriculum->id,
                        (int) $curriculum->course_id,
                        $subjectsPerCurriculum,
                        $slotPlan,
                        $courseSubjectMap,
                        $globalSubjectIds,
                        $subjectUnits
                    );
                }
            });

        $totalAssignments = (int) DB::table('course_curriculum_subjects')->count();
        $this->command->info(
            'CourseCurriculumSubjectMatrixSeeder: processed '
            . $processedCurricula
            . ' curriculum row(s), inserted '
            . $createdRows
            . ' subject assignment(s), total course_curriculum_subjects = '
            . $totalAssignments
            . '.'
        );
    }

    private function hasRequiredTables()
    {
        return Schema::hasTable('course_curricula')
            && Schema::hasTable('course_curriculum_subjects')
            && Schema::hasTable('subjects')
            && Schema::hasTable('year_blocks')
            && Schema::hasTable('semesters');
    }

    private function ensureYearBlocks()
    {
        $now = now();
        $labels = ['1st Year', '2nd Year', '3rd Year', '4th Year'];

        foreach ($labels as $label) {
            DB::table('year_blocks')->updateOrInsert(
                ['label' => $label],
                ['label' => $label, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        return DB::table('year_blocks')
            ->whereIn('label', $labels)
            ->pluck('id', 'label')
            ->map(function ($id) {
                return (int) $id;
            })
            ->all();
    }

    private function ensureSemesters()
    {
        $now = now();
        $names = ['First Semester', 'Second Semester', 'Summer Semester'];

        foreach ($names as $name) {
            DB::table('semesters')->updateOrInsert(
                ['name' => $name],
                ['name' => $name, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        return DB::table('semesters')
            ->whereIn('name', $names)
            ->pluck('id', 'name')
            ->map(function ($id) {
                return (int) $id;
            })
            ->all();
    }

    private function buildSlotPlan(array $yearBlockIds, array $semesterIds)
    {
        $plan = [];
        $yearOrder = ['1st Year', '2nd Year', '3rd Year', '4th Year'];
        $semesterOrder = ['First Semester', 'Second Semester', 'Summer Semester'];

        foreach ($yearOrder as $yearLabel) {
            if (!isset($yearBlockIds[$yearLabel])) {
                continue;
            }

            foreach ($semesterOrder as $semesterName) {
                if (!isset($semesterIds[$semesterName])) {
                    continue;
                }

                $plan[] = [
                    'year_block_id' => (int) $yearBlockIds[$yearLabel],
                    'semester_id' => (int) $semesterIds[$semesterName],
                ];
            }
        }

        return $plan;
    }

    private function seedCurriculumAssignments(
        $curriculumId,
        $courseId,
        $subjectsPerCurriculum,
        array $slotPlan,
        array $courseSubjectMap,
        array $globalSubjectIds,
        array $subjectUnits
    ) {
        $existingRows = DB::table('course_curriculum_subjects')
            ->where('course_curriculum_id', (int) $curriculumId)
            ->get(['subject_id', 'year_block_id', 'semester_id']);

        $existingByCombo = [];
        $existingBySubject = [];
        $existingCountsBySlot = [];

        foreach ($existingRows as $row) {
            $subjectId = (int) $row->subject_id;
            $yearBlockId = (int) $row->year_block_id;
            $semesterId = (int) $row->semester_id;

            $existingByCombo[$subjectId . ':' . $yearBlockId . ':' . $semesterId] = true;
            $existingBySubject[$subjectId] = true;

            $slotKey = $yearBlockId . ':' . $semesterId;
            if (!isset($existingCountsBySlot[$slotKey])) {
                $existingCountsBySlot[$slotKey] = 0;
            }

            $existingCountsBySlot[$slotKey]++;
        }

        $needed = (int) $subjectsPerCurriculum - count($existingRows);
        if ($needed <= 0) {
            return 0;
        }

        $courseSpecific = $courseSubjectMap[(int) $courseId] ?? [];
        $subjectPool = count($courseSpecific) ? array_values(array_unique($courseSpecific)) : [];

        if (count($subjectPool) < $subjectsPerCurriculum) {
            foreach ($globalSubjectIds as $subjectId) {
                if (!in_array((int) $subjectId, $subjectPool, true)) {
                    $subjectPool[] = (int) $subjectId;
                }

                if (count($subjectPool) >= max($subjectsPerCurriculum * 2, $subjectsPerCurriculum + 6)) {
                    break;
                }
            }
        }

        if (!count($subjectPool)) {
            return 0;
        }

        $poolCount = count($subjectPool);
        $slotCount = count($slotPlan);
        $subjectOffset = (int) $curriculumId % $poolCount;
        $slotOffset = (int) $curriculumId % $slotCount;

        $rows = [];
        $inserted = 0;
        $attempts = 0;
        $maxAttempts = max($poolCount * $slotCount * 2, 80);

        while ($inserted < $needed && $attempts < $maxAttempts) {
            $poolIndex = ($subjectOffset + $attempts) % $poolCount;
            $slotIndex = ($slotOffset + $attempts) % $slotCount;

            $subjectId = (int) $subjectPool[$poolIndex];
            $slot = $slotPlan[$slotIndex];
            $yearBlockId = (int) $slot['year_block_id'];
            $semesterId = (int) $slot['semester_id'];

            $comboKey = $subjectId . ':' . $yearBlockId . ':' . $semesterId;
            if (isset($existingByCombo[$comboKey]) || isset($existingBySubject[$subjectId])) {
                $attempts++;
                continue;
            }

            $slotKey = $yearBlockId . ':' . $semesterId;
            $displayOrder = isset($existingCountsBySlot[$slotKey]) ? (int) $existingCountsBySlot[$slotKey] : 0;

            $rows[] = [
                'course_curriculum_id' => (int) $curriculumId,
                'subject_id' => $subjectId,
                'year_block_id' => $yearBlockId,
                'semester_id' => $semesterId,
                'credited_units' => isset($subjectUnits[$subjectId]) ? (float) $subjectUnits[$subjectId] : 3.0,
                'display_order' => $displayOrder,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $existingByCombo[$comboKey] = true;
            $existingBySubject[$subjectId] = true;
            $existingCountsBySlot[$slotKey] = $displayOrder + 1;
            $inserted++;
            $attempts++;
        }

        if (count($rows)) {
            DB::table('course_curriculum_subjects')->insert($rows);
        }

        return $inserted;
    }
}
