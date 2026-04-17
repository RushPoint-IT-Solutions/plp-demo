<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StudentProfileBackfillSeeder extends Seeder
{
    private $profileColumns = [];

    public function run()
    {
        if (app()->environment('production')) {
            throw new \RuntimeException('StudentProfileBackfillSeeder cannot run in production.');
        }

        if (!Schema::hasTable('students') || !Schema::hasTable('student_profiles')) {
            $this->command->warn('StudentProfileBackfillSeeder: required tables are missing; skipping.');
            return;
        }

        $this->profileColumns = Schema::getColumnListing('student_profiles');
        $studentCount = (int) DB::table('students')->count();

        if ($studentCount === 0) {
            $this->command->info('StudentProfileBackfillSeeder: no students found.');
            return;
        }

        $processed = 0;
        $this->command->info('StudentProfileBackfillSeeder: syncing profiles for ' . $studentCount . ' student(s)...');
        $this->command->getOutput()->progressStart($studentCount);

        DB::table('students')
            ->orderBy('id')
            ->chunkById(250, function ($students) use (&$processed) {
                foreach ($students as $student) {
                    $this->upsertStudentProfile($student);
                    $processed++;
                    $this->command->getOutput()->progressAdvance();
                }
            });

        $this->command->getOutput()->progressFinish();
        $this->command->info('StudentProfileBackfillSeeder: completed. Profiles synced: ' . $processed . '.');
    }

    private function upsertStudentProfile($student)
    {
        $studentNo = trim((string) data_get($student, 'student_no'));
        if ($studentNo === '') {
            return;
        }

        $existingByStudentNo = null;
        $existingByStudentId = null;

        if ($this->hasProfileColumn('student_no')) {
            $existingByStudentNo = DB::table('student_profiles')
                ->where('student_no', $studentNo)
                ->orderByDesc('id')
                ->first();
        }

        if ($this->hasProfileColumn('student_id')) {
            $existingByStudentId = DB::table('student_profiles')
                ->where('student_id', (int) data_get($student, 'id'))
                ->orderByDesc('id')
                ->first();
        }

        $existing = $existingByStudentNo ? $existingByStudentNo : $existingByStudentId;

        $forcePreferred = ($studentNo === '2025-000001');
        $preferred = $this->buildPreferredProfileData($student);

        if ($forcePreferred) {
            $preferred = array_merge($preferred, $this->juanMiguelProfileData());
        }

        $payload = $this->buildPayload($student, $preferred, $existing, $forcePreferred);

        if (!$existing) {
            $payload['created_at'] = now();
            $payload['updated_at'] = now();

            DB::table('student_profiles')->insert($payload);
            return;
        }

        $payload['updated_at'] = now();
        DB::table('student_profiles')->where('id', $existing->id)->update($payload);
    }

    private function buildPayload($student, array $preferred, $existing, $forcePreferred)
    {
        $payload = [];

        if ($this->hasProfileColumn('student_id')) {
            $payload['student_id'] = (int) data_get($student, 'id');
        }

        if ($this->hasProfileColumn('student_no')) {
            $payload['student_no'] = trim((string) data_get($student, 'student_no'));
        }

        foreach ($preferred as $field => $preferredValue) {
            if (!$this->hasProfileColumn($field)) {
                continue;
            }

            $currentValue = $existing ? data_get($existing, $field) : null;
            $value = $this->selectProfileValue($currentValue, $preferredValue, $forcePreferred);

            if ($value === null && !$this->isBooleanField($field)) {
                continue;
            }

            $payload[$field] = $value;
        }

        return $payload;
    }

    private function selectProfileValue($currentValue, $preferredValue, $forcePreferred)
    {
        if ($forcePreferred) {
            return $preferredValue;
        }

        if ($this->hasValue($currentValue)) {
            return $currentValue;
        }

        return $preferredValue;
    }

    private function hasValue($value)
    {
        if ($value === null) {
            return false;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        return true;
    }

    private function hasProfileColumn($column)
    {
        return in_array($column, $this->profileColumns, true);
    }

    private function isBooleanField($field)
    {
        return in_array($field, [
            'same_as_present',
            'is_orphan',
            'is_first_gen',
            'is_4ps',
            'has_disability',
            'is_foreign',
            'mother_pensioner',
            'father_pensioner',
            'working_student',
            'has_scholarship',
            'first_in_family_college',
            'profile_complete',
        ], true);
    }

    private function buildPreferredProfileData($student)
    {
        $studentId = (int) data_get($student, 'id');
        $studentNo = trim((string) data_get($student, 'student_no'));
        $nameParts = $this->splitStudentName((string) data_get($student, 'name'));

        $gender = trim((string) data_get($student, 'sex'));
        if ($gender === '') {
            $gender = 'Male';
        }

        $dateOfBirth = sprintf(
            '%04d-%02d-%02d',
            2000 + ($studentId % 7),
            ($studentId % 12) + 1,
            ($studentId % 27) + 1
        );

        $mobile = '09' . str_pad((string) (($studentId * 7919) % 1000000000), 9, '0', STR_PAD_LEFT);
        $email = $this->buildStudentEmail($studentNo, $studentId);

        $streetNumber = 10 + ($studentId % 90);
        $street = $streetNumber . ' Mabini Street';

        $yearBlockLabel = trim((string) data_get($student, 'year_level'));
        if ($yearBlockLabel === '') {
            $yearBlockLabel = trim((string) optional(data_get($student, 'yearBlock'))->label);
        }

        return [
            'first_name' => $nameParts['first_name'],
            'middle_name' => $nameParts['middle_name'],
            'last_name' => $nameParts['last_name'],
            'gender' => $gender,
            'nationality' => 'Filipino',
            'religion' => 'Roman Catholic',
            'civil_status' => 'Single',
            'date_of_birth' => $dateOfBirth,
            'place_of_birth' => 'Pasig City',
            'mobile_number' => $mobile,
            'student_email' => $email,
            'present_street' => $street,
            'present_barangay' => 'San Isidro',
            'present_municipality' => 'Pasig',
            'present_province' => 'Metro Manila',
            'present_region' => 'NCR',
            'present_zipcode' => '1600',
            'permanent_street' => $street,
            'permanent_barangay' => 'San Isidro',
            'permanent_municipality' => 'Pasig',
            'permanent_province' => 'Metro Manila',
            'permanent_region' => 'NCR',
            'permanent_zipcode' => '1600',
            'same_as_present' => true,
            'is_orphan' => false,
            'is_first_gen' => false,
            'is_4ps' => false,
            'has_disability' => false,
            'is_foreign' => false,
            'profile_complete' => true,
            'junior_school' => 'Pasig National High School',
            'senior_school' => 'Pasig Senior High School',
            'shs_track_strand' => 'STEM',
            'year_level' => $yearBlockLabel,
        ];
    }

    private function juanMiguelProfileData()
    {
        return [
            'first_name' => 'Juan Miguel',
            'middle_name' => 'Santos',
            'last_name' => 'Dela Cruz',
            'gender' => 'Male',
            'nationality' => 'Filipino',
            'religion' => 'Roman Catholic',
            'civil_status' => 'Single',
            'date_of_birth' => '2004-05-14',
            'place_of_birth' => 'Pasig City',
            'mobile_number' => '09171234567',
            'student_email' => 'student@plp.local',
            'present_street' => '12 Mabini Street',
            'present_barangay' => 'Kapasigan',
            'present_municipality' => 'Pasig',
            'present_province' => 'Metro Manila',
            'present_region' => 'NCR',
            'present_zipcode' => '1600',
            'permanent_street' => '12 Mabini Street',
            'permanent_barangay' => 'Kapasigan',
            'permanent_municipality' => 'Pasig',
            'permanent_province' => 'Metro Manila',
            'permanent_region' => 'NCR',
            'permanent_zipcode' => '1600',
            'same_as_present' => true,
            'is_orphan' => false,
            'is_first_gen' => true,
            'is_4ps' => false,
            'has_disability' => false,
            'is_foreign' => false,
            'mother_firstname' => 'Maria',
            'mother_middlename' => 'Reyes',
            'mother_lastname' => 'Dela Cruz',
            'mother_contact' => '09181234567',
            'mother_occupation' => 'Vendor',
            'mother_pensioner' => false,
            'father_firstname' => 'Jose',
            'father_middlename' => 'Castro',
            'father_lastname' => 'Dela Cruz',
            'father_contact' => '09191234567',
            'father_occupation' => 'Driver',
            'father_pensioner' => false,
            'guardian_firstname' => 'Ana',
            'guardian_middlename' => 'Luna',
            'guardian_lastname' => 'Dela Cruz',
            'guardian_contact' => '09201234567',
            'guardian_occupation' => 'Office Staff',
            'guardian_address' => '12 Mabini Street, Kapasigan, Pasig',
            'parent_marital_status' => 'Married',
            'monthly_family_income' => '20000-30000',
            'number_of_siblings' => 3,
            'household_members' => 6,
            'dependents' => 2,
            'junior_school' => 'Pasig City Science HS',
            'senior_school' => 'Pasig City Science SHS',
            'shs_track_strand' => 'STEM',
            'no_k12' => false,
            'lrn' => '123456789012',
            'family_income_source' => 'Employment',
            'living_situation' => 'With Parents',
            'working_student' => false,
            'has_scholarship' => true,
            'first_in_family_college' => true,
            'profile_complete' => true,
        ];
    }

    private function splitStudentName($fullName)
    {
        $fullName = trim((string) $fullName);

        $result = [
            'first_name' => null,
            'middle_name' => null,
            'last_name' => null,
        ];

        if ($fullName === '') {
            return $result;
        }

        if (strpos($fullName, ',') !== false) {
            $parts = explode(',', $fullName, 2);
            $lastName = trim((string) $parts[0]);
            $remaining = trim((string) data_get($parts, '1'));
            $tokens = preg_split('/\s+/', $remaining);

            $result['last_name'] = $lastName !== '' ? $lastName : null;
            $result['first_name'] = isset($tokens[0]) ? trim((string) $tokens[0]) : null;

            if (count($tokens) > 1) {
                $result['middle_name'] = trim(implode(' ', array_slice($tokens, 1)));
            }

            return $result;
        }

        $tokens = preg_split('/\s+/', $fullName);
        if (!is_array($tokens) || count($tokens) === 0) {
            return $result;
        }

        $result['first_name'] = trim((string) $tokens[0]);

        if (count($tokens) >= 2) {
            $result['last_name'] = trim((string) $tokens[count($tokens) - 1]);
        }

        if (count($tokens) > 2) {
            $result['middle_name'] = trim(implode(' ', array_slice($tokens, 1, -1)));
        }

        return $result;
    }

    private function buildStudentEmail($studentNo, $studentId)
    {
        $localPart = strtolower(preg_replace('/[^a-z0-9]+/i', '.', (string) $studentNo));
        $localPart = trim((string) $localPart, '.');

        if ($localPart === '') {
            $localPart = 'student-' . (string) $studentId;
        }

        return $localPart . '@plp.local';
    }
}
