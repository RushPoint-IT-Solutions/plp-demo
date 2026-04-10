<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HighVolumeAvailableSubjectSeeder extends Seeder
{
    public function run()
    {
        $targetCount = 5000;
        $existingCount = (int) DB::table('subjects')
            ->where('code', 'like', 'FLSUB-%')
            ->whereNull('faculty_id')
            ->count();

        if ($existingCount >= $targetCount) {
            $this->command->info('HighVolumeAvailableSubjectSeeder: target already satisfied (' . $existingCount . '/' . $targetCount . ').');
            return;
        }

        $courseIds = DB::table('courses')
            ->orderBy('id')
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->values()
            ->all();

        $termIds = DB::table('academic_terms')
            ->orderBy('school_year', 'desc')
            ->orderBy('id')
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->values()
            ->all();

        if (!count($courseIds) || !count($termIds)) {
            $this->command->warn('HighVolumeAvailableSubjectSeeder skipped: missing courses or academic terms.');
            return;
        }

        $gradingStatusId = DB::table('subject_grading_statuses')
            ->where('code', 'Open For Encoding')
            ->value('id');

        if (!$gradingStatusId) {
            $gradingStatusId = DB::table('subject_grading_statuses')->min('id');
        }

        $yearSections = ['1-A', '1-B', '2-A', '2-B', '3-A', '3-B', '4-A', '4-B'];
        $days = ['M,W', 'T,TH', 'W,F', 'TH,F', 'F,S'];
        $timeSlots = [
            ['07:00AM', '08:30AM'],
            ['08:30AM', '10:00AM'],
            ['10:00AM', '11:30AM'],
            ['01:00PM', '02:30PM'],
            ['02:30PM', '04:00PM'],
            ['04:00PM', '05:30PM'],
        ];

        $toCreate = $targetCount - $existingCount;
        $nextSequence = $existingCount + 1;
        $chunkSize = 500;

        $this->command->info('HighVolumeAvailableSubjectSeeder: creating ' . $toCreate . ' available subjects...');
        $this->command->getOutput()->progressStart($toCreate);

        while ($toCreate > 0) {
            $currentChunk = $toCreate > $chunkSize ? $chunkSize : $toCreate;
            $rows = [];

            for ($i = 0; $i < $currentChunk; $i++) {
                $seed = $nextSequence + $i;
                $slot = $timeSlots[$seed % count($timeSlots)];

                $rows[] = [
                    'code' => 'FLSUB-' . str_pad((string) $seed, 5, '0', STR_PAD_LEFT),
                    'name' => 'Faculty Loading Subject ' . $seed,
                    'is_subject_file_record' => 1,
                    'units' => 3.0,
                    'lec' => 2,
                    'lab' => 1,
                    'is_core' => 0,
                    'is_applied' => 1,
                    'is_specialized' => 0,
                    'days' => $days[$seed % count($days)],
                    'time_start' => $slot[0],
                    'time_end' => $slot[1],
                    'room' => (string) (100 + ($seed % 40)),
                    'faculty_id' => null,
                    'year_section' => $yearSections[$seed % count($yearSections)],
                    'course_id' => $courseIds[$seed % count($courseIds)],
                    'academic_term_id' => $termIds[$seed % count($termIds)],
                    'grading_status_id' => $gradingStatusId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $this->command->getOutput()->progressAdvance();
            }

            DB::table('subjects')->insert($rows);
            $nextSequence += $currentChunk;
            $toCreate -= $currentChunk;
        }

        $this->command->getOutput()->progressFinish();
        $this->command->info('HighVolumeAvailableSubjectSeeder: done.');
    }
}
