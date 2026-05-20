<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CompactFacultyLoadSeeder extends Seeder
{
    private const DEFAULT_MAX_LOAD_UNITS = 30;

    private const FACULTY_ROWS = [
        'FAC-001' => ['name' => 'Abejo, Maria', 'department' => 'College of Business and Accountancy'],
        'FAC-002' => ['name' => 'Reyes, Daniel', 'department' => 'College of Business and Accountancy'],
        'FAC-003' => ['name' => 'Santos, Clara', 'department' => 'College of Computer Studies'],
        'FAC-004' => ['name' => 'Cruz, Miguel', 'department' => 'College of Computer Studies'],
        'FAC-005' => ['name' => 'Garcia, Elena', 'department' => 'College of Education'],
        'FAC-006' => ['name' => 'Mendoza, Rafael', 'department' => 'College of Nursing'],
    ];

    public function run()
    {
        if (!Schema::hasTable('faculties')) {
            $this->command->warn('CompactFacultyLoadSeeder: faculties table is missing.');
            return;
        }

        DB::beginTransaction();
        try {
            $facultyIds = $this->upsertCompactFaculty();
            $this->clearGeneratedSubjectFacultyAssignments();
            $this->syncTeacherAllowedSubjects($facultyIds);
            $this->removeExcessFaculty($facultyIds);
            $this->setDefaultMaxLoads($facultyIds);

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }

        $this->command->info('CompactFacultyLoadSeeder: reduced faculty list to ' . count(self::FACULTY_ROWS) . ', cleared generated subject teachers, and set max load to 30 units.');
    }

    private function upsertCompactFaculty(): array
    {
        $facultyIds = [];

        foreach (self::FACULTY_ROWS as $code => $row) {
            $payload = [
                'name' => $row['name'],
                'updated_at' => now(),
            ];

            if (Schema::hasColumn('faculties', 'department')) {
                $payload['department'] = $row['department'];
            }
            if (Schema::hasColumn('faculties', 'employment_type')) {
                $payload['employment_type'] = 'Full-time Teacher';
            }

            $existingId = (int) DB::table('faculties')->where('code', $code)->value('id');
            if ($existingId > 0) {
                DB::table('faculties')->where('id', $existingId)->update($payload);
                $facultyIds[$code] = $existingId;
                continue;
            }

            $payload['code'] = $code;
            $payload['created_at'] = now();
            $facultyIds[$code] = (int) DB::table('faculties')->insertGetId($payload);
        }

        return $facultyIds;
    }

    private function clearGeneratedSubjectFacultyAssignments(): void
    {
        if (!Schema::hasTable('subjects') || !Schema::hasColumn('subjects', 'faculty_id')) {
            return;
        }

        $query = DB::table('subjects');
        if (Schema::hasColumn('subjects', 'is_subject_file_record')) {
            $query->where(function ($query) {
                if (Schema::hasColumn('subjects', 'is_subject_file_record')) {
                    $query->whereNull('is_subject_file_record')
                        ->orWhere('is_subject_file_record', 0);
                }
            });
        }

        $payload = [
            'faculty_id' => null,
            'updated_at' => now(),
        ];
        if (Schema::hasColumn('subjects', 'faculty')) {
            $payload['faculty'] = null;
        }

        $query->update($payload);
    }

    private function facultyPoolForProgram(string $program, array $facultyIds): array
    {
        if ($program === 'BSA') {
            return [$facultyIds['FAC-001'], $facultyIds['FAC-002']];
        }
        if (in_array($program, ['BSCS', 'BSIT'], true)) {
            return [$facultyIds['FAC-003'], $facultyIds['FAC-004']];
        }
        if ($program === 'BSED-ENG') {
            return [$facultyIds['FAC-005']];
        }
        if ($program === 'BSN') {
            return [$facultyIds['FAC-006']];
        }

        return array_values($facultyIds);
    }

    private function leastLoadedFaculty(array $pool, array $loads): int
    {
        usort($pool, function ($left, $right) use ($loads) {
            return ($loads[$left] ?? 0) <=> ($loads[$right] ?? 0);
        });

        return (int) $pool[0];
    }

    private function syncTeacherAllowedSubjects(array $facultyIds): void
    {
        if (!Schema::hasTable('teacher_allowed_subjects') || !Schema::hasTable('subjects')) {
            return;
        }

        DB::table('teacher_allowed_subjects')
            ->whereNotIn('faculty_id', array_values($facultyIds))
            ->delete();

        $subjects = DB::table('subjects')
            ->whereNotNull('faculty_id')
            ->where('faculty_id', '>', 0)
            ->get(['id', 'faculty_id']);

        foreach ($subjects as $subject) {
            DB::table('teacher_allowed_subjects')->updateOrInsert(
                [
                    'faculty_id' => (int) $subject->faculty_id,
                    'subject_id' => (int) $subject->id,
                ],
                [
                    'assigned_by_user_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function removeExcessFaculty(array $facultyIds): void
    {
        $keepIds = array_values($facultyIds);

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'faculty_id')) {
            DB::table('users')
                ->whereNotNull('faculty_id')
                ->whereNotIn('faculty_id', $keepIds)
                ->delete();
        }

        if (Schema::hasTable('master_faculty_files') && Schema::hasColumn('master_faculty_files', 'source_faculty_id')) {
            DB::table('master_faculty_files')
                ->whereNotNull('source_faculty_id')
                ->whereNotIn('source_faculty_id', $keepIds)
                ->update(['source_faculty_id' => null, 'updated_at' => now()]);
        }

        DB::table('faculties')
            ->whereNotIn('id', $keepIds)
            ->delete();
    }

    private function setDefaultMaxLoads(array $facultyIds): void
    {
        if (!Schema::hasColumn('faculties', 'max_load_units')) {
            return;
        }

        foreach ($facultyIds as $facultyId) {
            DB::table('faculties')->where('id', (int) $facultyId)->update([
                'max_load_units' => self::DEFAULT_MAX_LOAD_UNITS,
                'updated_at' => now(),
            ]);
        }
    }

    private function dominantAssignedTerm(array $facultyIds, string $unitExpression): ?array
    {
        if (!Schema::hasTable('academic_terms')) {
            return null;
        }

        $term = DB::table('subjects')
            ->join('academic_terms as at', 'at.id', '=', 'subjects.academic_term_id')
            ->select(
                'at.school_year',
                'at.term',
                DB::raw('COUNT(subjects.id) as subject_count'),
                DB::raw('SUM(' . $unitExpression . ') as total_load')
            )
            ->whereIn('subjects.faculty_id', array_values($facultyIds))
            ->groupBy('at.school_year', 'at.term')
            ->orderBy('at.school_year', 'desc')
            ->orderBy('total_load', 'desc')
            ->orderBy('subject_count', 'desc')
            ->first();

        if (!$term) {
            return null;
        }

        return [
            'school_year' => (string) $term->school_year,
            'term' => (string) $term->term,
        ];
    }
}
