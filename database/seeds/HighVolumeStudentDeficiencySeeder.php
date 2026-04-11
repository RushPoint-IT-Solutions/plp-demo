<?php

use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HighVolumeStudentDeficiencySeeder extends Seeder
{
    public function run()
    {
        if (!Schema::hasTable('student_deficiencies') || !Schema::hasTable('students')) {
            $this->command->warn('HighVolumeStudentDeficiencySeeder: required tables not found; skipping.');
            return;
        }

        $target = $this->resolveTargetCount();
        $existingCount = (int) DB::table('student_deficiencies')->count();

        if ($existingCount >= $target) {
            $this->command->info('HighVolumeStudentDeficiencySeeder: target already satisfied (' . $existingCount . '/' . $target . ').');
            return;
        }

        $studentIds = DB::table('students')
            ->orderBy('id')
            ->pluck('id')
            ->all();

        if (!count($studentIds)) {
            $this->command->warn('HighVolumeStudentDeficiencySeeder: no students found; skipping.');
            return;
        }

        $faker = FakerFactory::create(env('FAKER_LOCALE', 'en_PH'));
        $departments = ['Library', 'Cashier', 'Registrar', 'Guidance', 'Accounting', 'Clinic'];
        $chunkSize = 500;
        $toCreate = $target - $existingCount;
        $updatedBy = 'System Registrar';
        $baselineDate = now()->subDays(45);

        $this->command->info('HighVolumeStudentDeficiencySeeder: creating ' . $toCreate . ' deficiency record(s)...');
        $this->command->getOutput()->progressStart($toCreate);

        for ($created = 0; $created < $toCreate; $created += $chunkSize) {
            $batchCount = min($chunkSize, $toCreate - $created);
            $rows = [];

            for ($i = 0; $i < $batchCount; $i++) {
                $seed = $existingCount + $created + $i + 1;
                $studentId = $studentIds[($seed - 1) % count($studentIds)];
                $department = $departments[$seed % count($departments)];
                $dateToday = $baselineDate->copy()->addDays($seed % 60);
                $submissionDate = $dateToday->copy()->addDays(5 + ($seed % 6));
                $isCompleted = ($seed % 3) === 0;
                $complianceDate = $isCompleted ? $submissionDate->copy()->addDays(($seed % 4) + 1) : null;

                $rows[] = [
                    'student_id' => $studentId,
                    'department' => $department,
                    'remarks' => $department . ' deficiency #' . str_pad((string) $seed, 4, '0', STR_PAD_LEFT) . ' - ' . $faker->sentence(6),
                    'date_today' => $dateToday->toDateString(),
                    'submission_date' => $submissionDate->toDateString(),
                    'is_completed' => $isCompleted,
                    'compliance_date' => $complianceDate ? $complianceDate->toDateString() : null,
                    'updated_by' => $updatedBy,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $this->command->getOutput()->progressAdvance();
            }

            DB::table('student_deficiencies')->insert($rows);
        }

        $this->command->getOutput()->progressFinish();
        $this->command->info('HighVolumeStudentDeficiencySeeder: done. Total deficiencies = ' . $target . '.');
    }

    private function resolveTargetCount()
    {
        $defaultTarget = 5000;
        $fromEnv = env('SEED_HIGH_VOLUME_STUDENT_DEFICIENCIES_TARGET', env('HIGH_VOLUME_STUDENT_DEFICIENCY_TARGET', $defaultTarget));
        $target = (int) $fromEnv;

        return $target > 0 ? $target : $defaultTarget;
    }
}
