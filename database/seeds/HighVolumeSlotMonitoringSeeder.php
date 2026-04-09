<?php

use App\Course;
use App\SlotMonitoring;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HighVolumeSlotMonitoringSeeder extends Seeder
{
    public function run()
    {
        if (!Schema::hasTable('slot_monitorings')) {
            $this->command->warn('HighVolumeSlotMonitoringSeeder: slot_monitorings table not found.');
            return;
        }

        if (!Schema::hasTable('courses')) {
            $this->command->warn('HighVolumeSlotMonitoringSeeder: courses table not found.');
            return;
        }

        $courses = Course::query()
            ->orderBy('id')
            ->get(['id', 'code']);

        if ($courses->isEmpty()) {
            $this->command->warn('HighVolumeSlotMonitoringSeeder: no courses available for slot generation.');
            return;
        }

        $target = $this->resolveTargetCount();
        $existingCount = (int) SlotMonitoring::query()->count();

        if ($existingCount >= $target) {
            $this->command->info('HighVolumeSlotMonitoringSeeder: target already satisfied (' . $existingCount . '/' . $target . ').');
            return;
        }

        $toCreate = $target - $existingCount;
        $chunkSize = 500;

        $actorUserId = (int) (User::query()->where('module', 'registrar')->orderBy('id')->value('id') ?: 0);
        if ($actorUserId <= 0) {
            $actorUserId = (int) (User::query()->orderBy('id')->value('id') ?: 0);
        }

        $schoolYears = $this->buildSchoolYears();
        $semesters = ['First', 'Second', 'Summer'];
        $days = ['M', 'T', 'W', 'TH', 'F', 'S'];

        $this->command->info('HighVolumeSlotMonitoringSeeder: creating ' . $toCreate . ' slot monitoring records...');
        $this->command->getOutput()->progressStart($toCreate);

        $sequence = $existingCount + 1;
        $remaining = $toCreate;

        while ($remaining > 0) {
            $batchCount = $remaining > $chunkSize ? $chunkSize : $remaining;
            $rows = [];

            for ($i = 0; $i < $batchCount; $i++) {
                $index = $sequence + $i;
                $course = $courses[$index % $courses->count()];
                $schoolYear = $schoolYears[$index % count($schoolYears)];
                $semester = $semesters[$index % count($semesters)];

                $yearLevel = ($index % 4) + 1;
                $block = chr(65 + ($index % 5));
                $courseCode = trim((string) $course->code);
                if ($courseCode === '') {
                    $courseCode = 'PRG';
                }

                $section = $courseCode . ' ' . $yearLevel . '-' . $block;
                $subject = 'Subject ' . str_pad((string) $index, 5, '0', STR_PAD_LEFT);

                $startHour = 7 + ($index % 10);
                $endHour = $startHour + 2;
                $schedule = $days[$index % count($days)]
                    . ' | '
                    . str_pad((string) $startHour, 2, '0', STR_PAD_LEFT)
                    . ':00-'
                    . str_pad((string) $endHour, 2, '0', STR_PAD_LEFT)
                    . ':00 | RM#'
                    . (($index % 120) + 1);

                $totalSlots = 30 + ($index % 41);
                $enrolledSlots = $index % ($totalSlots + 1);

                $rows[] = [
                    'school_year' => $schoolYear,
                    'semester' => $semester,
                    'course_id' => (int) $course->id,
                    'section' => $section,
                    'subject' => $subject,
                    'schedule' => $schedule,
                    'total_slots' => $totalSlots,
                    'enrolled_slots' => $enrolledSlots,
                    'updated_by_user_id' => $actorUserId > 0 ? $actorUserId : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $this->command->getOutput()->progressAdvance();
            }

            DB::table('slot_monitorings')->insert($rows);

            $sequence += $batchCount;
            $remaining -= $batchCount;
        }

        $this->command->getOutput()->progressFinish();
        $this->command->info('HighVolumeSlotMonitoringSeeder: done. Total slot monitoring rows = ' . ((int) SlotMonitoring::query()->count()) . '.');
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

        if (isset($decoded['seeder']) && is_array($decoded['seeder'])) {
            if (isset($decoded['seeder']['slot_monitoring'])) {
                $slotMonitoringTarget = (int) $decoded['seeder']['slot_monitoring'];
                if ($slotMonitoringTarget > 0) {
                    return $slotMonitoringTarget;
                }
            }

            if (isset($decoded['seeder']['slots'])) {
                $slotTarget = (int) $decoded['seeder']['slots'];
                if ($slotTarget > 0) {
                    return $slotTarget;
                }
            }
        }

        return $defaultTarget;
    }

    private function buildSchoolYears()
    {
        $currentYear = (int) date('Y');

        return [
            ($currentYear - 1) . '-' . $currentYear,
            $currentYear . '-' . ($currentYear + 1),
            ($currentYear + 1) . '-' . ($currentYear + 2),
            ($currentYear - 2) . '-' . ($currentYear - 1),
        ];
    }
}
