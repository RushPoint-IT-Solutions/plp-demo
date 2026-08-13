<?php

use App\AcademicTerm;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;

class StudentMasterlistImportSeeder extends Seeder
{
    /**
     * Imports the real 2nd Semester 2025-2026 student masterlist
     * (storage/app/imports/plp-students-2025-2026-2nd-sem.csv, generated from
     * the registrar's students.xlsx "ALL PROGRAM" sheet) into students,
     * student_profiles, student_section_assignments, and login users.
     *
     * Run with: php artisan db:seed --class=StudentMasterlistImportSeeder
     *
     * @return void
     */
    private const CSV_PATH = 'imports/plp-students-2025-2026-2nd-sem.csv';

    private const PROGRAM_CODE_MAP = [
        'BSHM' => 'BSHM',
        'BSIT' => 'BSIT',
        'BSBA' => 'BSBA',
        'BSN' => 'BSN',
        'BSED FIL' => 'BSED-FIL',
        'BSED ENG' => 'BSED-ENG',
        'B E E D' => 'BEED',
        'BSENT' => 'BSENT',
        'AB PSYCH' => 'AB PSYCH',
        'BSA' => 'BSA',
        'BSECE' => 'BSECE',
        'BSCS' => 'BSCS',
        'BSED MATH' => 'BSED-MATH',
    ];

    private const YEAR_BLOCK_LABEL_MAP = [
        '1' => '1st Year',
        '2' => '2nd Year',
        '3' => '3rd Year',
        '4' => '4th Year',
    ];

    public function run()
    {
        if (!Schema::hasTable('students') || !Schema::hasTable('users') || !Schema::hasTable('courses')) {
            $this->command->warn('StudentMasterlistImportSeeder: required tables are missing.');
            return;
        }

        $csvPath = storage_path('app/' . self::CSV_PATH);
        if (!file_exists($csvPath)) {
            $this->command->warn("StudentMasterlistImportSeeder: CSV not found at {$csvPath}. Skipping.");
            return;
        }

        $academicTermId = $this->resolveAcademicTermId();
        if (!$academicTermId) {
            $this->command->warn('StudentMasterlistImportSeeder: could not resolve the 2025-2026 Second academic term.');
            return;
        }

        $courseIdByCode = DB::table('courses')->pluck('id', 'code');
        $yearBlockIdByLabel = Schema::hasTable('year_blocks')
            ? DB::table('year_blocks')->pluck('id', 'label')
            : collect();

        $hasStudentProfiles = Schema::hasTable('student_profiles');
        $hasSectionAssignments = Schema::hasTable('student_section_assignments');
        $profileColumns = $hasStudentProfiles ? Schema::getColumnListing('student_profiles') : [];

        $rows = $this->readCsv($csvPath);
        $total = count($rows);

        $this->command->info("StudentMasterlistImportSeeder: importing {$total} student(s)...");
        $this->command->getOutput()->progressStart($total);

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $usersCreated = 0;

        DB::transaction(function () use (
            $rows,
            $academicTermId,
            $courseIdByCode,
            $yearBlockIdByLabel,
            $hasStudentProfiles,
            $hasSectionAssignments,
            $profileColumns,
            &$created,
            &$updated,
            &$skipped,
            &$usersCreated
        ) {
            foreach ($rows as $row) {
                $this->command->getOutput()->progressAdvance();

                $studentNo = trim((string) ($row['student_no'] ?? ''));
                if ($studentNo === '') {
                    $skipped++;
                    continue;
                }

                $programKey = strtoupper(trim((string) ($row['program_abbrev'] ?? '')));
                $courseCode = self::PROGRAM_CODE_MAP[$programKey] ?? null;
                $courseId = $courseCode ? ($courseIdByCode[$courseCode] ?? null) : null;

                $yearLevelKey = trim((string) ($row['year_level'] ?? ''));
                $yearBlockLabel = self::YEAR_BLOCK_LABEL_MAP[$yearLevelKey] ?? null;
                $yearBlockId = $yearBlockLabel ? ($yearBlockIdByLabel[$yearBlockLabel] ?? null) : null;

                $surname = trim((string) ($row['surname'] ?? ''));
                $givenName = trim((string) ($row['given_name'] ?? ''));
                $middleName = trim((string) ($row['middle_name'] ?? ''));
                $displayName = trim($surname . ', ' . trim($givenName . ' ' . $middleName));

                $sexRaw = strtoupper(trim((string) ($row['sex'] ?? '')));
                $sex = $sexRaw === 'F' ? 'Female' : ($sexRaw === 'M' ? 'Male' : null);

                $dob = trim((string) ($row['date_of_birth'] ?? ''));
                $age = null;
                if ($dob !== '') {
                    try {
                        $age = Carbon::parse($dob)->age;
                    } catch (\Throwable $e) {
                        $age = null;
                    }
                }

                $now = now();
                $studentPayload = [
                    'name' => $displayName,
                    'sex' => $sex,
                    'age' => $age,
                    'course_id' => $courseId,
                    'year_block_id' => $yearBlockId,
                    'academic_term_id' => $academicTermId,
                    'updated_at' => $now,
                ];

                $existingId = (int) DB::table('students')->where('student_no', $studentNo)->value('id');
                if ($existingId) {
                    DB::table('students')->where('id', $existingId)->update($studentPayload);
                    $studentId = $existingId;
                    $updated++;
                } else {
                    $studentPayload['student_no'] = $studentNo;
                    $studentPayload['created_at'] = $now;
                    $studentId = (int) DB::table('students')->insertGetId($studentPayload);
                    $created++;
                }

                if ($hasStudentProfiles) {
                    $this->upsertStudentProfile($profileColumns, $studentId, $studentNo, [
                        'first_name' => $givenName ?: null,
                        'middle_name' => $middleName ?: null,
                        'last_name' => $surname ?: null,
                        'gender' => $sex,
                        'nationality' => 'Filipino',
                        'date_of_birth' => $dob ?: null,
                        'place_of_birth' => trim((string) ($row['place_of_birth'] ?? '')) ?: null,
                        'mobile_number' => trim((string) ($row['contact_number'] ?? '')) ?: null,
                        'student_email' => trim((string) ($row['email'] ?? '')) ?: null,
                        'present_street' => trim((string) ($row['address'] ?? '')) ?: null,
                        'present_zipcode' => trim((string) ($row['zip_code'] ?? '')) ?: null,
                        'profile_complete' => false,
                    ]);
                }

                if ($hasSectionAssignments && $academicTermId) {
                    $section = mb_substr(trim((string) ($row['section'] ?? '')), 0, 80);
                    if ($section !== '') {
                        DB::table('student_section_assignments')->updateOrInsert(
                            ['academic_term_id' => $academicTermId, 'student_id' => $studentId],
                            [
                                'course_id' => $courseId,
                                'year_block_id' => $yearBlockId,
                                'section' => $section,
                                'status' => 'active',
                                'approval_status' => 'auto_approved',
                                'updated_at' => $now,
                                'created_at' => $now,
                            ]
                        );
                    }
                }

                $defaultPassword = 'PLP-' . $studentNo;
                $existingUserId = (int) DB::table('users')->where('username', $studentNo)->value('id');
                $userPayload = [
                    'name' => $displayName,
                    'module' => 'student',
                    'student_id' => $studentId,
                    'updated_at' => $now,
                ];

                if ($existingUserId) {
                    DB::table('users')->where('id', $existingUserId)->update($userPayload);
                } else {
                    $userPayload['username'] = $studentNo;
                    $userPayload['password'] = Hash::make($defaultPassword);
                    $userPayload['force_password_reset'] = true;
                    $userPayload['created_at'] = $now;
                    DB::table('users')->insert($userPayload);
                    $usersCreated++;
                }
            }
        }, 3);

        $this->command->getOutput()->progressFinish();
        $this->command->info(
            "StudentMasterlistImportSeeder: students -> {$created} created, {$updated} updated, {$skipped} skipped. "
            . "Logins created: {$usersCreated}."
        );
    }

    private function upsertStudentProfile(array $profileColumns, int $studentId, string $studentNo, array $fields)
    {
        $payload = [];
        foreach ($fields as $column => $value) {
            if (in_array($column, $profileColumns, true)) {
                $payload[$column] = $value;
            }
        }

        $now = now();
        $existingId = (int) DB::table('student_profiles')->where('student_id', $studentId)->value('id');

        if ($existingId) {
            $payload['updated_at'] = $now;
            DB::table('student_profiles')->where('id', $existingId)->update($payload);
            return;
        }

        if (in_array('student_id', $profileColumns, true)) {
            $payload['student_id'] = $studentId;
        }
        if (in_array('student_no', $profileColumns, true)) {
            $payload['student_no'] = $studentNo;
        }
        $payload['created_at'] = $now;
        $payload['updated_at'] = $now;

        DB::table('student_profiles')->insert($payload);
    }

    private function resolveAcademicTermId(): ?int
    {
        if (!Schema::hasTable('academic_terms')) {
            return null;
        }

        $term = AcademicTerm::query()->firstOrCreate(
            ['canonical_key' => strtolower('2025-2026|second')],
            ['school_year' => '2025-2026', 'term' => 'Second']
        );

        // The dashboard picks the "active" term by status priority first
        // (Open for Enrollment > Open > Draft > ...), then by school_year/id.
        // Without this, a later-created Draft term (e.g. Summer, created by
        // ClassListDataBackfillSeeder) can outrank this one on id alone even
        // though this is where all the real students actually are.
        if (Schema::hasColumn('academic_terms', 'status') && $term->status !== 'Open for Enrollment') {
            DB::table('academic_terms')->where('id', $term->id)->update(['status' => 'Open for Enrollment']);
        }

        return (int) $term->id;
    }

    private function readCsv(string $path): array
    {
        $rows = [];
        $handle = fopen($path, 'r');
        if (!$handle) {
            return $rows;
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return $rows;
        }

        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) === 1 && $data[0] === null) {
                continue;
            }
            $rows[] = array_combine($header, array_pad($data, count($header), null));
        }

        fclose($handle);
        return $rows;
    }
}
