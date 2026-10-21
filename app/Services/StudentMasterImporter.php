<?php

declare(strict_types=1);

namespace App\Services;

use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class StudentMasterImporter
{
    private $reader;

    public function __construct(StudentMasterReader $reader)
    {
        $this->reader = $reader;
    }

    public function preview(string $path): array
    {
        $normalize = function ($number) { return mb_strtolower((string) $number, 'UTF-8'); };
        $existing = array_fill_keys(array_map($normalize, DB::table('students')->pluck('student_no')->all()), true);
        $profiles = array_fill_keys(array_map($normalize, DB::table('student_profiles')->whereNotNull('student_no')->pluck('student_no')->all()), true);
        $seen = [];
        $result = ['rows' => 0, 'new' => 0, 'existing' => 0, 'active' => 0, 'inactive' => 0,
            'errors' => [], 'error_count' => 0, 'invalid_rows' => 0, 'duplicate_count' => 0,
            'warnings' => [], 'warning_count' => 0, 'sample' => []];
        foreach ($this->reader->rows($path) as $row) {
            $result['rows']++;
            if ($result['rows'] > 20000) {
                throw new RuntimeException('Import at most 20,000 students per workbook.');
            }
            $record = $this->map($row);
            if ($record['errors']) {
                $result['invalid_rows']++;
            }
            foreach ($record['errors'] as $error) {
                $this->issue($result, 'errors', 'Row ' . $row['row'] . ': ' . $error);
            }
            foreach ($record['warnings'] as $warning) {
                $this->issue($result, 'warnings', 'Row ' . $row['row'] . ': ' . $warning);
            }
            $number = mb_strtolower($record['student']['student_no'], 'UTF-8');
            if (isset($seen[$number])) {
                $result['duplicate_count']++;
                $this->issue($result, 'errors', 'Row ' . $row['row'] . ': duplicate student number in this workbook.');
            }
            $seen[$number] = true;
            $isExisting = isset($existing[$number]) || isset($profiles[$number]);
            if (!$record['errors']) {
                $result[$isExisting ? 'existing' : 'new']++;
            }
            $result[$record['active'] ? 'active' : 'inactive']++;
            if (count($result['sample']) < 8) {
                $result['sample'][] = ['row' => $row['row'], 'number' => $record['student']['student_no'],
                    'name' => $record['student']['name'], 'active' => $record['active'], 'existing' => $isExisting];
            }
        }
        if ($result['rows'] === 0) {
            $this->issue($result, 'errors', 'The worksheet contains no student records.');
        }
        return $result;
    }

    public function import(string $path, bool $skipInvalid = false): array
    {
        $preview = $this->preview($path);
        if (!$preview['rows'] || $preview['duplicate_count'] || ($preview['error_count'] && !$skipInvalid)) {
            throw new RuntimeException('Resolve the preview errors before importing.');
        }
        if (!Schema::hasTable('student_master_sources')) {
            throw new RuntimeException('Apply the StudentMaster database migration before importing.');
        }
        $studentColumns = array_flip(Schema::getColumnListing('students'));
        $profileColumns = array_flip(Schema::getColumnListing('student_profiles'));
        $hash = hash_file('sha256', $path);
        return DB::transaction(function () use ($path, $studentColumns, $profileColumns, $hash) {
            $result = ['created' => 0, 'skipped' => 0, 'skipped_invalid' => 0, 'active' => 0, 'inactive' => 0];
            foreach ($this->reader->rows($path) as $row) {
                $record = $this->map($row);
                if ($record['errors']) {
                    $result['skipped_invalid']++;
                    continue;
                }
                $number = $record['student']['student_no'];
                if (DB::table('students')->where('student_no', $number)->exists()
                    || DB::table('student_profiles')->where('student_no', $number)->exists()) {
                    $result['skipped']++;
                    continue;
                }
                $now = date('Y-m-d H:i:s');
                $studentId = DB::table('students')->insertGetId(array_intersect_key(
                    array_merge($record['student'], ['school_year' => '', 'semester' => '', 'student_type' => '',
                        'created_at' => $now, 'updated_at' => $now]), $studentColumns
                ));
                DB::table('student_profiles')->insert(array_intersect_key(array_merge($record['profile'], [
                    'student_id' => $studentId, 'student_no' => $number, 'created_at' => $now, 'updated_at' => $now,
                ]), $profileColumns));
                DB::table('student_master_sources')->insert([
                    'student_id' => $studentId, 'is_active' => $record['active'],
                    'student_type_code' => $row['fields']['fldStudType'] ?? null,
                    'source_row' => $row['row'], 'file_sha256' => $hash,
                    'source_data' => json_encode($row['source'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                    'created_at' => $now, 'updated_at' => $now,
                ]);
                $result['created']++;
                $result[$record['active'] ? 'active' : 'inactive']++;
            }
            return $result;
        });
    }

    public function map(array $row): array
    {
        $fields = $row['fields'];
        $get = function ($key) use ($fields) {
            $value = trim((string) ($fields[$key] ?? ''));
            return in_array(strtoupper($value), ['', 'NULL', 'N/A', '-'], true) ? null : $value;
        };
        $number = $get('fldStudNo') ?? '';
        $name = trim(implode(' ', array_filter([$get('fldLastName') ? $get('fldLastName') . ',' : null,
            $get('fldFirstName'), $get('fldMiddleName'), $get('fldNameSuffix')])));
        $errors = [];
        $warnings = [];
        if ($number === '' || mb_strlen($number) > 255 || preg_match('/[\x00-\x1F]/', $number)) {
            $errors[] = 'Missing or invalid student number.';
        }
        if (!$get('fldFirstName') || !$get('fldLastName') || mb_strlen($name) > 255) {
            $errors[] = 'A first name and last name of at most 255 characters combined are required.';
        }
        if (!in_array($fields['fldActive'] ?? '', ['0', '1'], true)) {
            $errors[] = 'Active status must be 0 or 1.';
        }
        $sex = ['M' => 'Male', 'F' => 'Female', 'MALE' => 'Male', 'FEMALE' => 'Female'][strtoupper($get('fldSex') ?? '')] ?? null;
        $profile = [];
        $mapping = [
            'fldFirstName' => 'first_name', 'fldLastName' => 'last_name', 'fldMiddleName' => 'middle_name',
            'fldNameSuffix' => 'suffix', 'fldMobileNo' => 'mobile_number', 'fldEmailAdd' => 'student_email',
            'fldZipCd' => 'present_zipcode', 'fldPlaceofBirth' => 'place_of_birth',
            'fldMotherOcc' => 'mother_occupation', 'fldFatherOcc' => 'father_occupation',
            'fldGuardianAddress' => 'guardian_address', 'fldLastSchool' => 'school_last_attended',
            'fldLastSchoolYr' => 'school_last_attended_year_graduated', 'fldElementary' => 'elementary_school',
            'fldElementaryYr' => 'elementary_year_graduated', 'fldHighSchool' => 'high_school',
            'fldHighSchoolYr' => 'high_school_year_graduated',
        ];
        foreach ($mapping as $source => $target) {
            $profile[$target] = $get($source);
        }
        $profile['gender'] = $sex;
        $profile['present_street'] = trim(implode(' ', array_filter([$get('fldHouseNo'), $get('fldAddress')]))) ?: null;
        foreach (['mother' => 'Mother', 'father' => 'Father', 'guardian' => 'Guardian'] as $target => $source) {
            $profile[$target . '_contact'] = $get('fld' . $source . 'MobileNo') ?: $get('fld' . $source . 'PhoneNo');
        }
        foreach (['fldCitizenship' => 'nationality', 'fldReligion' => 'religion', 'fldCivilStatus' => 'civil_status'] as $source => $target) {
            // Numeric legacy lookup codes need their original lookup tables; preserve them in source details.
            $value = $get($source);
            if ($value !== null && !is_numeric($value)) {
                $profile[$target] = $value;
            }
        }
        $birth = $get('fldDateofBirth');
        if ($birth !== null) {
            $date = $this->birthDate($birth);
            if ($date === null) {
                $warnings[] = 'Birth date retained in source details because it could not be interpreted safely.';
            } else {
                $profile['date_of_birth'] = $date;
            }
        }
        foreach ($profile as $key => $value) {
            if ($value !== null && mb_strlen($value) > 255) {
                unset($profile[$key]);
                $warnings[] = $key . ' exceeds the profile field limit; the complete value remains in source details.';
            }
        }
        return ['student' => ['student_no' => $number, 'name' => $name, 'sex' => $sex],
            'profile' => $profile, 'active' => ($fields['fldActive'] ?? '') === '1',
            'errors' => $errors, 'warnings' => $warnings];
    }

    private function birthDate(string $value): ?string
    {
        if (is_numeric($value)) {
            $days = (int) floor((float) $value);
            if ($days > 0 && $days < 80000) {
                $date = (new DateTimeImmutable('1899-12-30'))->modify('+' . $days . ' days')->format('Y-m-d');
                return $date <= date('Y-m-d') ? $date : null;
            }
            return null;
        }
        foreach (['Y-m-d', 'Y-m-d H:i:s', 'm/d/Y', 'n/j/Y'] as $format) {
            $date = DateTimeImmutable::createFromFormat('!' . $format, $value);
            $errors = DateTimeImmutable::getLastErrors();
            if ($date && (!$errors || (!$errors['warning_count'] && !$errors['error_count']))
                && $date->format('Y-m-d') <= date('Y-m-d')) {
                return $date->format('Y-m-d');
            }
        }
        return null;
    }

    private function issue(array &$result, string $kind, string $message): void
    {
        $count = $kind === 'errors' ? 'error_count' : 'warning_count';
        $result[$count]++;
        if (count($result[$kind]) < 50) {
            $result[$kind][] = $message;
        }
    }
}
