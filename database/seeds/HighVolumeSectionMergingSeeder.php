<?php

use App\SectionMergingOperation;
use App\SlotMonitoring;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HighVolumeSectionMergingSeeder extends Seeder
{
    public function run()
    {
        if (!Schema::hasTable('section_merging_operations')) {
            $this->command->warn('HighVolumeSectionMergingSeeder: section_merging_operations table not found.');
            return;
        }

        if (!Schema::hasTable('slot_monitorings')) {
            $this->command->warn('HighVolumeSectionMergingSeeder: slot_monitorings table not found.');
            return;
        }

        $slotRows = SlotMonitoring::query()
            ->orderBy('id')
            ->get([
                'id',
                'school_year',
                'semester',
                'course_id',
                'section',
                'subject',
                'total_slots',
                'enrolled_slots',
            ]);

        if ($slotRows->count() < 2) {
            $this->command->warn('HighVolumeSectionMergingSeeder: not enough slot records to build merge operations.');
            return;
        }

        $eligibleGroups = $this->buildEligibleGroups($slotRows->all());
        if (count($eligibleGroups) === 0) {
            $this->command->warn('HighVolumeSectionMergingSeeder: no eligible slot groups with at least two slots in the same school year/semester/program.');
            return;
        }

        $target = $this->resolveTargetCount();
        $existingCount = (int) SectionMergingOperation::query()->count();

        if ($existingCount >= $target) {
            $this->command->info('HighVolumeSectionMergingSeeder: target already satisfied (' . $existingCount . '/' . $target . ').');
            return;
        }

        $toCreate = $target - $existingCount;
        $chunkSize = 500;

        $actorUserId = (int) (User::query()->where('module', 'registrar')->orderBy('id')->value('id') ?: 0);
        if ($actorUserId <= 0) {
            $actorUserId = (int) (User::query()->orderBy('id')->value('id') ?: 0);
        }

        $this->command->info('HighVolumeSectionMergingSeeder: creating ' . $toCreate . ' section merge operation records...');
        $this->command->getOutput()->progressStart($toCreate);

        $remaining = $toCreate;
        $sequence = $existingCount + 1;
        $eligibleGroupCount = count($eligibleGroups);

        while ($remaining > 0) {
            $batchCount = $remaining > $chunkSize ? $chunkSize : $remaining;
            $rows = [];

            for ($i = 0; $i < $batchCount; $i++) {
                $globalIndex = $sequence + $i;
                $group = $eligibleGroups[$globalIndex % $eligibleGroupCount];
                $groupCount = count($group);

                if ($groupCount < 2) {
                    $this->command->getOutput()->progressAdvance();
                    continue;
                }

                $sourceIndex = $globalIndex % $groupCount;
                $targetIndex = ($sourceIndex + 1) % $groupCount;

                $source = $group[$sourceIndex];
                $targetSlot = $group[$targetIndex];

                $sourceTotalSlots = max(1, (int) $source->total_slots);
                $sourceEnrolledSlots = max(0, (int) $source->enrolled_slots);
                if ($sourceEnrolledSlots > $sourceTotalSlots) {
                    $sourceEnrolledSlots = $sourceTotalSlots;
                }

                $targetTotalBefore = max(1, (int) $targetSlot->total_slots);
                $targetEnrolledBefore = max(0, (int) $targetSlot->enrolled_slots);
                if ($targetEnrolledBefore > $targetTotalBefore) {
                    $targetEnrolledBefore = $targetTotalBefore;
                }

                $targetTotalAfter = min(65535, $targetTotalBefore + $sourceTotalSlots);
                $targetEnrolledAfter = min(65535, $targetEnrolledBefore + $sourceEnrolledSlots);

                $rows[] = [
                    'school_year' => (string) $source->school_year,
                    'semester' => (string) $source->semester,
                    'source_slot_monitoring_id' => (int) $source->id,
                    'target_slot_monitoring_id' => (int) $targetSlot->id,
                    'source_course_id' => (int) $source->course_id,
                    'target_course_id' => (int) $targetSlot->course_id,
                    'source_section' => (string) $source->section,
                    'target_section' => (string) $targetSlot->section,
                    'source_subject' => (string) $source->subject,
                    'target_subject' => (string) $targetSlot->subject,
                    'source_total_slots' => $sourceTotalSlots,
                    'source_enrolled_slots' => $sourceEnrolledSlots,
                    'target_total_slots_before' => $targetTotalBefore,
                    'target_enrolled_slots_before' => $targetEnrolledBefore,
                    'target_total_slots_after' => $targetTotalAfter,
                    'target_enrolled_slots_after' => $targetEnrolledAfter,
                    'merge_status' => 'completed',
                    'merged_by_user_id' => $actorUserId > 0 ? $actorUserId : null,
                    'merged_at' => now()->subDays($globalIndex % 90)->subMinutes($globalIndex % 1440),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $this->command->getOutput()->progressAdvance();
            }

            if (!empty($rows)) {
                DB::table('section_merging_operations')->insert($rows);
            }

            $sequence += $batchCount;
            $remaining -= $batchCount;
        }

        $this->command->getOutput()->progressFinish();
        $this->command->info('HighVolumeSectionMergingSeeder: done. Total section merge rows = ' . ((int) SectionMergingOperation::query()->count()) . '.');
    }

    /**
     * @param array<int, \App\SlotMonitoring> $slotRows
     * @return array<int, array<int, \App\SlotMonitoring>>
     */
    private function buildEligibleGroups(array $slotRows)
    {
        $strictGroups = $this->buildGroupedSlots($slotRows, true, true);
        if (count($strictGroups) > 0) {
            return $strictGroups;
        }

        $courseScopedGroups = $this->buildGroupedSlots($slotRows, false, true);
        if (count($courseScopedGroups) > 0) {
            return $courseScopedGroups;
        }

        return $this->buildGroupedSlots($slotRows, false, false);
    }

    /**
     * @param array<int, \App\SlotMonitoring> $slotRows
     * @param bool $includeSubject
     * @param bool $includeCourse
     * @return array<int, array<int, \App\SlotMonitoring>>
     */
    private function buildGroupedSlots(array $slotRows, $includeSubject, $includeCourse)
    {
        $groups = [];

        foreach ($slotRows as $slotRow) {
            $key = strtolower(trim((string) $slotRow->school_year))
                . '|'
                . strtolower(trim((string) $slotRow->semester));

            if ($includeCourse) {
                $key .= '|' . (int) $slotRow->course_id;
            }

            if ($includeSubject) {
                $key .= '|' . strtolower(trim((string) $slotRow->subject));
            }

            if (!array_key_exists($key, $groups)) {
                $groups[$key] = [];
            }

            $groups[$key][] = $slotRow;
        }

        $eligible = [];
        foreach ($groups as $groupRows) {
            if (count($groupRows) < 2) {
                continue;
            }

            $eligible[] = array_values($groupRows);
        }

        return $eligible;
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

        $candidates = [
            'section_merging',
            'section_merges',
            'section_merging_operations',
        ];

        foreach ($candidates as $candidateKey) {
            if (!array_key_exists($candidateKey, $decoded['seeder'])) {
                continue;
            }

            $target = (int) $decoded['seeder'][$candidateKey];
            if ($target > 0) {
                return $target;
            }
        }

        return $defaultTarget;
    }
}
