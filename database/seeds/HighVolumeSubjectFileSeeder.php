<?php

use App\Subject;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HighVolumeSubjectFileSeeder extends Seeder
{
    public function run()
    {
        $target = $this->resolveTargetCount();
        $existingCount = Subject::query()
            ->where('is_subject_file_record', true)
            ->count();

        if ($existingCount >= $target) {
            $this->command->info('HighVolumeSubjectFileSeeder: target already satisfied (' . $existingCount . '/' . $target . ').');
            return;
        }

        $toCreate = $target - $existingCount;
        $chunkSize = 500;
        $faker = FakerFactory::create(env('FAKER_LOCALE', 'en_PH'));

        $existingCodes = Subject::query()
            ->where('is_subject_file_record', true)
            ->pluck('code')
            ->map(function ($code) {
                return strtoupper((string) $code);
            })
            ->flip()
            ->all();

        $this->command->info('HighVolumeSubjectFileSeeder: creating ' . $toCreate . ' subject-file records...');
        $this->command->getOutput()->progressStart($toCreate);

        $nextSequence = $existingCount + 1;
        $remaining = $toCreate;

        while ($remaining > 0) {
            $batchCount = $remaining > $chunkSize ? $chunkSize : $remaining;
            $rows = [];

            for ($i = 0; $i < $batchCount; $i++) {
                $code = $this->nextUniqueCode($nextSequence, $existingCodes);
                $lec = $faker->randomElement([1, 2, 3]);
                $lab = $faker->randomElement([0, 1, 2, 3]);
                $core = $faker->boolean(35);
                $applied = !$core && $faker->boolean(40);
                $specialized = !$core && !$applied;

                $rows[] = [
                    'code' => $code,
                    'name' => ucfirst($faker->words($faker->numberBetween(2, 4), true)),
                    'units' => (float) ($lec + $lab),
                    'lec' => $lec,
                    'lab' => $lab,
                    'is_subject_file_record' => true,
                    'is_core' => $core,
                    'is_applied' => $applied,
                    'is_specialized' => $specialized,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $this->command->getOutput()->progressAdvance();
            }

            DB::table('subjects')->insert($rows);
            $remaining -= $batchCount;
        }

        $this->command->getOutput()->progressFinish();
        $this->command->info('HighVolumeSubjectFileSeeder: done. Total subject-file records = ' . $target . '.');
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

        if (isset($decoded['seeder']) && is_array($decoded['seeder']) && isset($decoded['seeder']['subjects'])) {
            $value = (int) $decoded['seeder']['subjects'];
            if ($value > 0) {
                return $value;
            }
        }

        return $defaultTarget;
    }

    private function nextUniqueCode(&$sequence, array &$existingCodes)
    {
        do {
            $candidate = 'SJF-' . str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
            $sequence++;
        } while (isset($existingCodes[$candidate]));

        $existingCodes[$candidate] = true;

        return $candidate;
    }
}
