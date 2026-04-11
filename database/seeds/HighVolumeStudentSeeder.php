<?php

use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HighVolumeStudentSeeder extends Seeder
{
    public function run()
    {
        if (!Schema::hasTable('students')) {
            $this->command->warn('HighVolumeStudentSeeder: students table not found; skipping.');
            return;
        }

        $target = $this->resolveTargetCount();
        $existingCount = (int) DB::table('students')->count();

        if ($existingCount >= $target) {
            $this->command->info('HighVolumeStudentSeeder: target already satisfied (' . $existingCount . '/' . $target . ').');
            $this->call(ClassListStudentEnrollmentBackfillSeeder::class);
            return;
        }

        $context = $this->resolveContext();
        if (!count($context['courses']) || !count($context['yearBlocks']) || !count($context['academicTerms'])) {
            $this->command->warn('HighVolumeStudentSeeder: missing course/year-term context; skipping.');
            return;
        }

        $faker = FakerFactory::create(env('FAKER_LOCALE', 'en_PH'));
        $chunkSize = 500;
        $toCreate = $target - $existingCount;
        $sequence = $this->resolveStartingSequence();

        $this->command->info('HighVolumeStudentSeeder: creating ' . $toCreate . ' student record(s)...');
        $this->command->getOutput()->progressStart($toCreate);

        for ($created = 0; $created < $toCreate; $created += $chunkSize) {
            $batchCount = min($chunkSize, $toCreate - $created);
            $rows = [];

            for ($i = 0; $i < $batchCount; $i++) {
                $seed = $sequence;
                $rows[] = $this->buildStudentRow($seed, $context, $faker);
                $sequence++;
                $this->command->getOutput()->progressAdvance();
            }

            DB::table('students')->insert($rows);
        }

        $this->command->getOutput()->progressFinish();
        $this->command->info('HighVolumeStudentSeeder: done. Total students = ' . $target . '.');

        // Ensure new students are visible in Class List section drill-down.
        $this->call(ClassListStudentEnrollmentBackfillSeeder::class);
    }

    private function resolveTargetCount()
    {
        $defaultTarget = 5000;

        $fromEnv = env('SEED_HIGH_VOLUME_STUDENTS_TARGET', env('HIGH_VOLUME_STUDENT_TARGET', $defaultTarget));
        $target = (int) $fromEnv;

        return $target > 0 ? $target : $defaultTarget;
    }

    private function resolveStartingSequence()
    {
        $lastStudentNo = (string) DB::table('students')
            ->where('student_no', 'like', 'HVSTU-%')
            ->orderBy('student_no', 'desc')
            ->value('student_no');

        if ($lastStudentNo === '') {
            return 1;
        }

        $raw = preg_replace('/[^0-9]/', '', $lastStudentNo);
        $lastNumber = (int) $raw;

        return $lastNumber > 0 ? $lastNumber + 1 : 1;
    }

    private function resolveContext()
    {
        $studentColumns = Schema::getColumnListing('students');

        $context = [
            'hasCourseId' => Schema::hasColumn('students', 'course_id'),
            'hasYearBlockId' => Schema::hasColumn('students', 'year_block_id'),
            'hasAcademicTermId' => Schema::hasColumn('students', 'academic_term_id'),
            'hasProgram' => in_array('program', $studentColumns, true),
            'hasYearLevel' => in_array('year_level', $studentColumns, true),
            'hasSchoolYear' => in_array('school_year', $studentColumns, true),
            'hasSemester' => in_array('semester', $studentColumns, true),
            'courses' => [],
            'yearBlocks' => [],
            'academicTerms' => [],
        ];

        if (Schema::hasTable('courses')) {
            $context['courses'] = DB::table('courses')
                ->orderBy('id')
                ->get(['id', 'code', 'name'])
                ->map(function ($row) {
                    return [
                        'id' => (int) $row->id,
                        'code' => trim((string) $row->code),
                        'name' => trim((string) $row->name),
                    ];
                })
                ->values()
                ->all();
        }

        if (Schema::hasTable('year_blocks')) {
            $context['yearBlocks'] = DB::table('year_blocks')
                ->orderBy('id')
                ->get(['id', 'label'])
                ->map(function ($row) {
                    return [
                        'id' => (int) $row->id,
                        'label' => trim((string) $row->label),
                    ];
                })
                ->values()
                ->all();
        }

        if (Schema::hasTable('academic_terms')) {
            $context['academicTerms'] = DB::table('academic_terms')
                ->orderBy('school_year', 'desc')
                ->orderBy('id')
                ->get(['id', 'school_year', 'term'])
                ->map(function ($row) {
                    return [
                        'id' => (int) $row->id,
                        'school_year' => trim((string) $row->school_year),
                        'term' => $this->normalizeTermLabel((string) $row->term),
                    ];
                })
                ->filter(function ($row) {
                    return $row['school_year'] !== '' && $row['term'] !== '';
                })
                ->values()
                ->all();
        }

        return $context;
    }

    private function buildStudentRow($seed, array $context, $faker)
    {
        $courseCount = count($context['courses']);
        $yearBlockCount = count($context['yearBlocks']);
        $termCount = count($context['academicTerms']);

        $course = $context['courses'][$seed % $courseCount];
        $yearBlock = $context['yearBlocks'][$seed % $yearBlockCount];
        $term = $context['academicTerms'][$seed % $termCount];

        $studentNo = 'HVSTU-' . str_pad((string) $seed, 6, '0', STR_PAD_LEFT);
        $registrationNo = 'HVREG-' . str_pad((string) $seed, 6, '0', STR_PAD_LEFT);

        $row = [
            'student_no' => $studentNo,
            'name' => $faker->lastName . ', ' . $faker->firstName . ' ' . strtoupper($faker->randomLetter),
            'sex' => $faker->randomElement(['Male', 'Female']),
            'age' => $faker->numberBetween(17, 26),
            'college' => 'College of Computer Studies',
            'curriculum' => 'Curriculum ' . $term['school_year'],
            'scholarship' => $faker->boolean(20) ? 'FREE EDUCATION' : null,
            'registration_no' => $registrationNo,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if ($context['hasProgram']) {
            $row['program'] = $course['code'] !== '' ? $course['code'] : $course['name'];
        }

        if ($context['hasYearLevel']) {
            $row['year_level'] = $yearBlock['label'];
        }

        if ($context['hasSchoolYear']) {
            $row['school_year'] = $term['school_year'];
        }

        if ($context['hasSemester']) {
            $row['semester'] = $term['term'];
        }

        if ($context['hasCourseId']) {
            $row['course_id'] = $course['id'];
        }

        if ($context['hasYearBlockId']) {
            $row['year_block_id'] = $yearBlock['id'];
        }

        if ($context['hasAcademicTermId']) {
            $row['academic_term_id'] = $term['id'];
        }

        return $row;
    }

    private function normalizeTermLabel($value)
    {
        $normalized = strtolower(trim((string) $value));
        if ($normalized === '') {
            return '';
        }

        if (strpos($normalized, 'summer') !== false) {
            return 'Summer';
        }

        if (strpos($normalized, 'second') !== false || strpos($normalized, '2nd') !== false || $normalized === '2') {
            return 'Second';
        }

        if (strpos($normalized, 'first') !== false || strpos($normalized, '1st') !== false || $normalized === '1') {
            return 'First';
        }

        return trim((string) $value);
    }
}
