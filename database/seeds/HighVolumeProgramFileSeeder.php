<?php

use App\Course;
use App\Department;
use App\Faculty;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HighVolumeProgramFileSeeder extends Seeder
{
    public function run()
    {
        if (!Schema::hasTable('courses')) {
            $this->command->warn('HighVolumeProgramFileSeeder: courses table not found.');
            return;
        }

        $this->ensureDepartments();

        $departmentIds = Department::query()
            ->orderBy('id')
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->values()
            ->all();

        if (!count($departmentIds)) {
            $this->command->warn('HighVolumeProgramFileSeeder: no departments available.');
            return;
        }

        $target = $this->resolveTargetCount();
        $existingCount = (int) Course::query()->count();

        if ($existingCount >= $target) {
            $this->command->info('HighVolumeProgramFileSeeder: target already satisfied (' . $existingCount . '/' . $target . ').');
            return;
        }

        $toCreate = $target - $existingCount;
        $chunkSize = 500;

        $facultyIds = Faculty::query()
            ->orderBy('id')
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->values()
            ->all();

        $existingCodes = Course::query()
            ->pluck('code')
            ->map(function ($code) {
                return strtoupper((string) $code);
            })
            ->flip()
            ->all();

        $accreditationLevels = [
            'Level I Accredited',
            'Level II Accredited',
            'Level III Accredited',
            'Level IV Accredited',
            'Pending Review',
        ];

        $trackCategories = ['Academic', 'TVL', 'Academic/TVL', null];

        $this->command->info('HighVolumeProgramFileSeeder: creating ' . $toCreate . ' program records...');
        $this->command->getOutput()->progressStart($toCreate);

        $sequence = $existingCount + 1;
        $remaining = $toCreate;

        while ($remaining > 0) {
            $batchCount = $remaining > $chunkSize ? $chunkSize : $remaining;
            $rows = [];

            for ($i = 0; $i < $batchCount; $i++) {
                $code = $this->nextUniqueCode($sequence, $existingCodes);
                $nameSeed = str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
                $departmentId = $departmentIds[$sequence % count($departmentIds)];
                $accreditationLevel = $accreditationLevels[$sequence % count($accreditationLevels)];
                $trackCategory = $trackCategories[$sequence % count($trackCategories)];
                $slots = 35 + ($sequence % 56);

                $facultyId = null;
                if (count($facultyIds)) {
                    $facultyId = $facultyIds[$sequence % count($facultyIds)];
                }

                $programName = 'Program ' . $nameSeed;

                $rows[] = [
                    'code' => $code,
                    'name' => $programName,
                    'program_type' => 'Degree',
                    'department_id' => $departmentId,
                    'description' => $programName,
                    'program_file' => $accreditationLevel,
                    'slots' => $slots,
                    'track_category' => $trackCategory,
                    'non_filipino' => ($sequence % 11 === 0) ? 1 : 0,
                    'dean_director_id' => $facultyId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $sequence++;
                $this->command->getOutput()->progressAdvance();
            }

            DB::table('courses')->insert($rows);
            $remaining -= $batchCount;
        }

        $this->command->getOutput()->progressFinish();
        $this->command->info('HighVolumeProgramFileSeeder: done. Total programs = ' . ((int) Course::query()->count()) . '.');
    }

    private function ensureDepartments()
    {
        if (Department::query()->exists()) {
            return;
        }

        $defaults = [
            ['code' => 'CCS', 'description' => 'College of Computer Studies'],
            ['code' => 'CBA', 'description' => 'College of Business Administration'],
            ['code' => 'COE', 'description' => 'College of Education'],
            ['code' => 'CON', 'description' => 'College of Nursing'],
        ];

        foreach ($defaults as $department) {
            Department::query()->updateOrCreate([
                'code' => $department['code'],
            ], [
                'description' => $department['description'],
            ]);
        }
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
        if (!is_array($decoded)) {
            return $defaultTarget;
        }

        if (isset($decoded['seeder']) && is_array($decoded['seeder']) && isset($decoded['seeder']['programs'])) {
            $value = (int) $decoded['seeder']['programs'];
            if ($value > 0) {
                return $value;
            }
        }

        return $defaultTarget;
    }

    private function nextUniqueCode(&$sequence, array &$existingCodes)
    {
        do {
            $candidate = 'PFG-' . str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
            $sequence++;
        } while (isset($existingCodes[strtoupper($candidate)]));

        $existingCodes[strtoupper($candidate)] = true;

        return $candidate;
    }
}
