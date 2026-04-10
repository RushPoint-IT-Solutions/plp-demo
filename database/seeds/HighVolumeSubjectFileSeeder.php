<?php

use App\Subject;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        $assignmentContext = $this->resolveAssignmentContext();

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
                $seed = $nextSequence;
                $code = $this->nextUniqueCode($nextSequence, $existingCodes);
                $lec = $faker->randomElement([1, 2, 3]);
                $lab = $faker->randomElement([0, 1, 2, 3]);
                $core = $faker->boolean(35);
                $applied = !$core && $faker->boolean(40);
                $specialized = !$core && !$applied;

                $rows[] = array_merge([
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
                ], $this->buildClassAssignmentPayload($seed, $assignmentContext));

                $this->command->getOutput()->progressAdvance();
            }

            DB::table('subjects')->insert($rows);
            $remaining -= $batchCount;
        }

        $this->command->getOutput()->progressFinish();
        $this->command->info('HighVolumeSubjectFileSeeder: done. Total subject-file records = ' . $target . '.');
    }

    private function resolveAssignmentContext()
    {
        $context = [
            'hasDays' => Schema::hasColumn('subjects', 'days'),
            'hasTimeStart' => Schema::hasColumn('subjects', 'time_start'),
            'hasTimeEnd' => Schema::hasColumn('subjects', 'time_end'),
            'hasRoom' => Schema::hasColumn('subjects', 'room'),
            'hasCourseId' => Schema::hasColumn('subjects', 'course_id'),
            'hasYearSection' => Schema::hasColumn('subjects', 'year_section'),
            'hasAcademicTermId' => Schema::hasColumn('subjects', 'academic_term_id'),
            'hasFacultyId' => Schema::hasColumn('subjects', 'faculty_id'),
            'courseIds' => [],
            'facultyIds' => [],
            'roomValues' => [],
            'academicTermIds' => [],
            'yearSections' => ['1-A', '1-B', '2-A', '2-B', '3-A', '3-B', '4-A', '4-B'],
            'dayOptions' => ['M,W', 'T,TH', 'W,F', 'TH,F', 'S'],
            'timeSlots' => [
                ['07:00AM', '08:30AM'],
                ['08:30AM', '10:00AM'],
                ['10:00AM', '11:30AM'],
                ['01:00PM', '02:30PM'],
                ['02:30PM', '04:00PM'],
                ['04:00PM', '05:30PM'],
            ],
        ];

        if ($context['hasCourseId']) {
            $context['courseIds'] = DB::table('courses')
                ->orderBy('id')
                ->pluck('id')
                ->map(function ($id) {
                    return (int) $id;
                })
                ->values()
                ->all();
        }

        if ($context['hasFacultyId']) {
            $context['facultyIds'] = DB::table('faculties')
                ->orderBy('id')
                ->pluck('id')
                ->map(function ($id) {
                    return (int) $id;
                })
                ->values()
                ->all();
        }

        if ($context['hasRoom'] && Schema::hasTable('rooms') && Schema::hasColumn('rooms', 'room_number')) {
            $context['roomValues'] = DB::table('rooms')
                ->whereNotNull('room_number')
                ->orderBy('id')
                ->pluck('room_number')
                ->map(function ($value) {
                    return trim((string) $value);
                })
                ->filter(function ($value) {
                    return $value !== '';
                })
                ->unique()
                ->values()
                ->all();
        }

        if (!count($context['roomValues'])) {
            $context['roomValues'] = ['101', '102', '201', '202', '301', '302'];
        }

        if ($context['hasAcademicTermId'] && Schema::hasTable('academic_terms')) {
            $context['academicTermIds'] = DB::table('academic_terms')
                ->orderBy('school_year', 'desc')
                ->orderBy('id')
                ->pluck('id')
                ->map(function ($id) {
                    return (int) $id;
                })
                ->values()
                ->all();
        }

        if ($context['hasAcademicTermId'] && !count($context['academicTermIds'])) {
            $yearStart = (int) date('Y');
            $schoolYear = $yearStart . '-' . ($yearStart + 1);
            $term = 'First Semester';
            $canonicalKey = strtolower($schoolYear . '|' . $term);

            $existing = DB::table('academic_terms')->where('canonical_key', $canonicalKey)->first(['id']);

            if ($existing) {
                $context['academicTermIds'][] = (int) $existing->id;
            } else {
                $context['academicTermIds'][] = (int) DB::table('academic_terms')->insertGetId([
                    'school_year' => $schoolYear,
                    'term' => $term,
                    'canonical_key' => $canonicalKey,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return $context;
    }

    private function buildClassAssignmentPayload($seed, array $context)
    {
        $payload = [];

        $slot = $context['timeSlots'][$seed % count($context['timeSlots'])];

        if ($context['hasDays']) {
            $payload['days'] = $context['dayOptions'][$seed % count($context['dayOptions'])];
        }

        if ($context['hasTimeStart']) {
            $payload['time_start'] = $slot[0];
        }

        if ($context['hasTimeEnd']) {
            $payload['time_end'] = $slot[1];
        }

        if ($context['hasRoom']) {
            $payload['room'] = $context['roomValues'][$seed % count($context['roomValues'])];
        }

        if ($context['hasCourseId'] && count($context['courseIds'])) {
            $payload['course_id'] = $context['courseIds'][$seed % count($context['courseIds'])];
        }

        if ($context['hasYearSection']) {
            $payload['year_section'] = $context['yearSections'][$seed % count($context['yearSections'])];
        }

        if ($context['hasAcademicTermId'] && count($context['academicTermIds'])) {
            $payload['academic_term_id'] = $context['academicTermIds'][$seed % count($context['academicTermIds'])];
        }

        if ($context['hasFacultyId'] && count($context['facultyIds'])) {
            $payload['faculty_id'] = $context['facultyIds'][$seed % count($context['facultyIds'])];
        }

        return $payload;
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
