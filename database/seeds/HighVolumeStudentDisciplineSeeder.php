<?php

use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HighVolumeStudentDisciplineSeeder extends Seeder
{
    public function run()
    {
        if (!$this->requiredTablesExist()) {
            $this->command->warn('HighVolumeStudentDisciplineSeeder: required tables not found; skipping.');
            return;
        }

        $targetStudents = $this->resolveTargetStudents();
        $this->ensureStudentVolume($targetStudents);

        $studentRows = DB::table('students')
            ->select('id', 'student_no', 'sex', 'course_id')
            ->orderBy('id')
            ->limit($targetStudents)
            ->get()
            ->map(function ($row) {
                return [
                    'id' => (int) $row->id,
                    'student_no' => (string) ($row->student_no ?: ''),
                    'sex' => (string) ($row->sex ?: ''),
                    'course_id' => $row->course_id ? (int) $row->course_id : null,
                ];
            })
            ->values()
            ->all();

        if (!count($studentRows)) {
            $this->command->warn('HighVolumeStudentDisciplineSeeder: no student records available; skipping.');
            return;
        }

        $faker = FakerFactory::create(env('FAKER_LOCALE', 'en_PH'));

        $this->ensureStudentProfiles($studentRows, $faker);
        $this->ensureDisciplineRoster($studentRows);
        $this->ensureDisciplineRecords($faker);

        $disciplineStudentCount = (int) DB::table('student_discipline_students')->count();
        $disciplineRecordCount = (int) DB::table('student_discipline_records')->count();

        $this->command->info(
            'HighVolumeStudentDisciplineSeeder: done. Discipline students=' . $disciplineStudentCount . ', records=' . $disciplineRecordCount . '.'
        );
    }

    private function requiredTablesExist()
    {
        return Schema::hasTable('students')
            && Schema::hasTable('student_profiles')
            && Schema::hasTable('student_discipline_students')
            && Schema::hasTable('student_discipline_records')
            && Schema::hasTable('student_discipline_student_types')
            && Schema::hasTable('student_discipline_case_types')
            && Schema::hasTable('student_discipline_action_types');
    }

    private function resolveTargetStudents()
    {
        $defaultTarget = 5000;
        $target = (int) env('SEED_HIGH_VOLUME_STUDENT_DISCIPLINE_TARGET', $defaultTarget);

        return $target > 0 ? $target : $defaultTarget;
    }

    private function resolveRecordsPerStudent()
    {
        $defaultPerStudent = 1;
        $value = (int) env('SEED_HIGH_VOLUME_STUDENT_DISCIPLINE_RECORDS_PER_STUDENT', $defaultPerStudent);

        return $value > 0 ? $value : $defaultPerStudent;
    }

    private function ensureStudentVolume($targetStudents)
    {
        $currentStudentCount = (int) DB::table('students')->count();
        if ($currentStudentCount >= $targetStudents) {
            return;
        }

        if (class_exists('HighVolumeStudentSeeder')) {
            $this->command->info(
                'HighVolumeStudentDisciplineSeeder: students=' . $currentStudentCount . '. Calling HighVolumeStudentSeeder to reach target ' . $targetStudents . '.'
            );
            $this->call(HighVolumeStudentSeeder::class);
        }
    }

    private function ensureStudentProfiles(array $studentRows, $faker)
    {
        $studentIds = array_map(function ($row) {
            return (int) $row['id'];
        }, $studentRows);

        $existingProfileIds = DB::table('student_profiles')
            ->whereIn('student_id', $studentIds)
            ->pluck('student_id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->all();

        $usedProfileStudentNos = DB::table('student_profiles')
            ->whereNotNull('student_no')
            ->where('student_no', '!=', '')
            ->pluck('student_no')
            ->map(function ($studentNo) {
                return strtolower(trim((string) $studentNo));
            })
            ->filter(function ($studentNo) {
                return $studentNo !== '';
            })
            ->values()
            ->all();

        $usedProfileStudentNoMap = array_fill_keys($usedProfileStudentNos, true);

        $existingMap = array_flip($existingProfileIds);
        $rowsToInsert = [];

        foreach ($studentRows as $student) {
            $studentId = (int) $student['id'];
            if (isset($existingMap[$studentId])) {
                continue;
            }

            $gender = $this->normalizeGender($student['sex']);
            $rowsToInsert[] = [
                'student_id' => $studentId,
                'student_no' => $this->reserveUniqueProfileStudentNo(
                    $student['student_no'],
                    $studentId,
                    $usedProfileStudentNoMap
                ),
                'gender' => $gender,
                'date_of_birth' => $faker->dateTimeBetween('-25 years', '-17 years')->format('Y-m-d'),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!count($rowsToInsert)) {
            return;
        }

        foreach (array_chunk($rowsToInsert, 500) as $chunk) {
            DB::table('student_profiles')->insert($chunk);
        }

        $this->command->info('HighVolumeStudentDisciplineSeeder: created ' . count($rowsToInsert) . ' student profile row(s).');
    }

    private function ensureDisciplineRoster(array $studentRows)
    {
        $studentTypeIds = DB::table('student_discipline_student_types')
            ->orderBy('id')
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->all();

        if (!count($studentTypeIds)) {
            $this->command->warn('HighVolumeStudentDisciplineSeeder: no student discipline student types found; skipping roster seeding.');
            return;
        }

        $studentIds = array_map(function ($row) {
            return (int) $row['id'];
        }, $studentRows);

        $existingRosterIds = DB::table('student_discipline_students')
            ->whereIn('student_id', $studentIds)
            ->pluck('student_id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->all();

        $existingMap = array_flip($existingRosterIds);
        $rowsToInsert = [];

        foreach ($studentRows as $index => $student) {
            $studentId = (int) $student['id'];
            if (isset($existingMap[$studentId])) {
                continue;
            }

            $rowsToInsert[] = [
                'student_id' => $studentId,
                'course_id' => $student['course_id'],
                'student_type_id' => $studentTypeIds[$index % count($studentTypeIds)],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!count($rowsToInsert)) {
            return;
        }

        foreach (array_chunk($rowsToInsert, 500) as $chunk) {
            DB::table('student_discipline_students')->insert($chunk);
        }

        $this->command->info('HighVolumeStudentDisciplineSeeder: created ' . count($rowsToInsert) . ' student discipline roster row(s).');
    }

    private function ensureDisciplineRecords($faker)
    {
        $recordsPerStudent = $this->resolveRecordsPerStudent();

        $disciplineStudents = DB::table('student_discipline_students as sds')
            ->join('students as s', 's.id', '=', 'sds.student_id')
            ->select('sds.id as discipline_student_id', 's.name as student_name')
            ->orderBy('sds.id')
            ->get()
            ->map(function ($row) {
                return [
                    'discipline_student_id' => (int) $row->discipline_student_id,
                    'student_name' => (string) ($row->student_name ?: ''),
                ];
            })
            ->values()
            ->all();

        if (!count($disciplineStudents)) {
            return;
        }

        $caseTypeIds = DB::table('student_discipline_case_types')
            ->orderBy('id')
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->all();

        $actionTypeIds = DB::table('student_discipline_action_types')
            ->orderBy('id')
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->all();

        if (!count($caseTypeIds) || !count($actionTypeIds)) {
            $this->command->warn('HighVolumeStudentDisciplineSeeder: case/action type lookups are incomplete; skipping record seeding.');
            return;
        }

        $existingCounts = DB::table('student_discipline_records')
            ->select('discipline_student_id', DB::raw('COUNT(*) as total'))
            ->groupBy('discipline_student_id')
            ->pluck('total', 'discipline_student_id')
            ->map(function ($count) {
                return (int) $count;
            })
            ->all();

        $rowsToInsert = [];
        $calledByOptions = [
            'Guidance Office',
            'Class Adviser',
            'Discipline Office',
            'Program Chair',
            'Dean\'s Office',
        ];
        $counselorOptions = [
            'Ms. Santos',
            'Mr. Ramos',
            'Guidance Team A',
            'Guidance Team B',
            'Guidance Office',
        ];

        foreach ($disciplineStudents as $index => $student) {
            $disciplineStudentId = (int) $student['discipline_student_id'];
            $existing = isset($existingCounts[$disciplineStudentId]) ? (int) $existingCounts[$disciplineStudentId] : 0;
            $needed = $recordsPerStudent - $existing;

            if ($needed <= 0) {
                continue;
            }

            for ($i = 0; $i < $needed; $i++) {
                $seed = $index + $i + 1;
                $incidentDate = now()->subDays(($seed % 280) + 1);
                $actionDate = $incidentDate->copy()->addDays(($seed % 14) + 1);
                $isCompleted = ($seed % 4) !== 0;

                $rowsToInsert[] = [
                    'discipline_student_id' => $disciplineStudentId,
                    'case_type_id' => $caseTypeIds[$seed % count($caseTypeIds)],
                    'action_type_id' => $actionTypeIds[$seed % count($actionTypeIds)],
                    'incident_date' => $incidentDate->toDateString(),
                    'walk_in' => ($seed % 3) === 0,
                    'called_by' => $calledByOptions[$seed % count($calledByOptions)],
                    'description' => $faker->sentence(14),
                    'action_date' => $isCompleted ? $actionDate->toDateString() : null,
                    'counselor' => $counselorOptions[$seed % count($counselorOptions)],
                    'remarks' => $isCompleted ? 'Completed follow-up and guidance notes captured.' : 'Pending follow-up session.',
                    'is_completed' => $isCompleted,
                    'updated_by' => 'System Seeder',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (!count($rowsToInsert)) {
            $this->command->info('HighVolumeStudentDisciplineSeeder: discipline records already satisfy target per student.');
            return;
        }

        $this->command->info('HighVolumeStudentDisciplineSeeder: creating ' . count($rowsToInsert) . ' discipline record(s)...');
        $this->command->getOutput()->progressStart(count($rowsToInsert));

        foreach (array_chunk($rowsToInsert, 500) as $chunk) {
            DB::table('student_discipline_records')->insert($chunk);
            $this->command->getOutput()->progressAdvance(count($chunk));
        }

        $this->command->getOutput()->progressFinish();
    }

    private function normalizeGender($value)
    {
        $normalized = strtolower(trim((string) $value));
        if ($normalized === 'female' || $normalized === 'f') {
            return 'Female';
        }

        return 'Male';
    }

    private function reserveUniqueProfileStudentNo($preferredStudentNo, $studentId, array &$usedStudentNoMap)
    {
        $preferred = trim((string) $preferredStudentNo);
        $preferredKey = strtolower($preferred);

        if ($preferred !== '' && !isset($usedStudentNoMap[$preferredKey])) {
            $usedStudentNoMap[$preferredKey] = true;
            return $preferred;
        }

        $base = 'HVPROF-' . str_pad((string) $studentId, 6, '0', STR_PAD_LEFT);
        $candidate = $base;
        $suffix = 1;

        while (isset($usedStudentNoMap[strtolower($candidate)])) {
            $candidate = $base . '-' . str_pad((string) $suffix, 2, '0', STR_PAD_LEFT);
            $suffix++;
        }

        $usedStudentNoMap[strtolower($candidate)] = true;

        return $candidate;
    }
}
