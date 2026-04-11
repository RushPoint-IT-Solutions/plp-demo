<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HighVolumeTransmutationRuleSeeder extends Seeder
{
    public function run()
    {
        if (!Schema::hasTable('transmutation_rules')) {
            $this->command->warn('HighVolumeTransmutationRuleSeeder: transmutation_rules table not found.');
            return;
        }

        $hasAcademicTermId = Schema::hasColumn('transmutation_rules', 'academic_term_id');
        $hasCourseId = Schema::hasColumn('transmutation_rules', 'course_id');

        if (!$hasAcademicTermId || !$hasCourseId) {
            $this->command->warn('HighVolumeTransmutationRuleSeeder: requires academic_term_id and course_id columns on transmutation_rules.');
            return;
        }

        $termRows = DB::table('academic_terms')
            ->orderBy('school_year', 'desc')
            ->orderBy('id')
            ->get(['id', 'school_year', 'term']);

        if ($termRows->isEmpty()) {
            $this->command->warn('HighVolumeTransmutationRuleSeeder: no academic_terms found.');
            return;
        }

        $courseRows = DB::table('courses')
            ->orderBy('id')
            ->get(['id', 'code', 'name']);

        if ($courseRows->isEmpty()) {
            $this->command->warn('HighVolumeTransmutationRuleSeeder: no courses found.');
            return;
        }

        $target = $this->resolveTargetCount();
        $existingCount = (int) DB::table('transmutation_rules')->count();

        if ($existingCount >= $target) {
            $this->command->info('HighVolumeTransmutationRuleSeeder: target already satisfied (' . $existingCount . '/' . $target . ').');
            return;
        }

        $toCreate = $target - $existingCount;
        $chunkSize = 500;
        $bands = $this->gradeBands();

        $termIds = $termRows->pluck('id')->map(function ($id) {
            return (int) $id;
        })->values()->all();

        $courseIds = $courseRows->pluck('id')->map(function ($id) {
            return (int) $id;
        })->values()->all();

        $termLookup = $termRows->keyBy('id')->all();
        $courseLookup = $courseRows->keyBy('id')->all();

        $hasSchoolYear = Schema::hasColumn('transmutation_rules', 'school_year');
        $hasTerm = Schema::hasColumn('transmutation_rules', 'term');
        $hasProgram = Schema::hasColumn('transmutation_rules', 'program');

        $existingKeys = DB::table('transmutation_rules')
            ->whereNotNull('academic_term_id')
            ->whereNotNull('course_id')
            ->select('academic_term_id', 'course_id', 'initial_from', 'initial_to')
            ->get()
            ->mapWithKeys(function ($row) {
                $key = (int) $row->academic_term_id
                    . '|'
                    . (int) $row->course_id
                    . '|'
                    . number_format((float) $row->initial_from, 2, '.', '')
                    . '|'
                    . number_format((float) $row->initial_to, 2, '.', '');

                return [$key => true];
            })
            ->all();

        $termCount = count($termIds);
        $courseCount = count($courseIds);
        $bandCount = count($bands);
        $combinationSpace = $termCount * $courseCount * $bandCount;

        if ($combinationSpace <= 0) {
            $this->command->warn('HighVolumeTransmutationRuleSeeder: no valid combinations available.');
            return;
        }

        $this->command->info('HighVolumeTransmutationRuleSeeder: creating ' . $toCreate . ' transmutation rows...');
        $this->command->getOutput()->progressStart($toCreate);

        $rows = [];
        $remaining = $toCreate;
        $cursor = 0;
        $attempts = 0;
        $maxAttempts = $combinationSpace + ($toCreate * 6);

        while ($remaining > 0 && $attempts < $maxAttempts) {
            $bandIndex = $cursor % $bandCount;
            $termIndex = (int) floor($cursor / $bandCount) % $termCount;
            $courseIndex = (int) floor($cursor / ($bandCount * $termCount)) % $courseCount;

            $band = $bands[$bandIndex];
            $academicTermId = $termIds[$termIndex];
            $courseId = $courseIds[$courseIndex];

            $key = $academicTermId
                . '|'
                . $courseId
                . '|'
                . number_format((float) $band['from'], 2, '.', '')
                . '|'
                . number_format((float) $band['to'], 2, '.', '');

            if (!isset($existingKeys[$key])) {
                $payload = [
                    'academic_term_id' => $academicTermId,
                    'course_id' => $courseId,
                    'initial_from' => $band['from'],
                    'initial_to' => $band['to'],
                    'transmuted_grade' => $band['grade'],
                    'code' => $band['code'],
                    'remarks' => $band['remarks'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if ($hasSchoolYear || $hasTerm) {
                    $termRow = $termLookup[$academicTermId] ?? null;
                    if ($termRow) {
                        if ($hasSchoolYear) {
                            $payload['school_year'] = (string) $termRow->school_year;
                        }
                        if ($hasTerm) {
                            $payload['term'] = (string) $termRow->term;
                        }
                    }
                }

                if ($hasProgram) {
                    $courseRow = $courseLookup[$courseId] ?? null;
                    if ($courseRow) {
                        $courseCode = trim((string) ($courseRow->code ?? ''));
                        $courseName = trim((string) ($courseRow->name ?? ''));
                        $payload['program'] = $courseCode !== '' ? $courseCode : $courseName;
                    }
                }

                $rows[] = $payload;
                $existingKeys[$key] = true;
                $remaining--;
                $this->command->getOutput()->progressAdvance();

                if (count($rows) >= $chunkSize) {
                    DB::table('transmutation_rules')->insert($rows);
                    $rows = [];
                }
            }

            $cursor++;
            $attempts++;
        }

        if (count($rows)) {
            DB::table('transmutation_rules')->insert($rows);
        }

        $this->command->getOutput()->progressFinish();

        if ($remaining > 0) {
            $this->command->warn('HighVolumeTransmutationRuleSeeder: created partial set. Missing ' . $remaining . ' rows due combination limits.');
        }

        $total = (int) DB::table('transmutation_rules')->count();
        $this->command->info('HighVolumeTransmutationRuleSeeder: done. Total rows = ' . $total . '.');
    }

    private function resolveTargetCount()
    {
        $defaultTarget = 5000;
        $configPath = base_path('.ultimate-architect.json');

        if (!file_exists($configPath)) {
            return $defaultTarget;
        }

        $raw = file_get_contents($configPath);
        if ($raw === false || trim($raw) === '') {
            return $defaultTarget;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded) || !isset($decoded['seeder']) || !is_array($decoded['seeder'])) {
            return $defaultTarget;
        }

        if (isset($decoded['seeder']['transmutation_rules'])) {
            $value = (int) $decoded['seeder']['transmutation_rules'];
            if ($value > 0) {
                return $value;
            }
        }

        if (isset($decoded['seeder']['transmutation'])) {
            $value = (int) $decoded['seeder']['transmutation'];
            if ($value > 0) {
                return $value;
            }
        }

        return $defaultTarget;
    }

    private function gradeBands()
    {
        return [
            ['from' => 97.00, 'to' => 100.00, 'grade' => 1.00, 'code' => 'A+', 'remarks' => 'Passed'],
            ['from' => 94.00, 'to' => 96.99, 'grade' => 1.25, 'code' => 'A', 'remarks' => 'Passed'],
            ['from' => 91.00, 'to' => 93.99, 'grade' => 1.50, 'code' => 'B+', 'remarks' => 'Passed'],
            ['from' => 88.00, 'to' => 90.99, 'grade' => 1.75, 'code' => 'B', 'remarks' => 'Passed'],
            ['from' => 85.00, 'to' => 87.99, 'grade' => 2.00, 'code' => 'C+', 'remarks' => 'Passed'],
            ['from' => 82.00, 'to' => 84.99, 'grade' => 2.25, 'code' => 'C', 'remarks' => 'Passed'],
            ['from' => 79.00, 'to' => 81.99, 'grade' => 2.50, 'code' => 'D+', 'remarks' => 'Passed'],
            ['from' => 76.00, 'to' => 78.99, 'grade' => 2.75, 'code' => 'D', 'remarks' => 'Passed'],
            ['from' => 75.00, 'to' => 75.99, 'grade' => 3.00, 'code' => 'P', 'remarks' => 'Passed'],
            ['from' => 0.00, 'to' => 74.99, 'grade' => 5.00, 'code' => 'F', 'remarks' => 'Failed'],
        ];
    }
}
