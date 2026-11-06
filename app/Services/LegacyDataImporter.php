<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/** Validates the three legacy exports before any business data is written. */
class LegacyDataImporter
{
    private $policy;
    private $master;
    private $summary = [];
    private $issues;

    public function __construct()
    {
        $this->policy = new LegacyImportPolicy();
        $this->master = new StudentMasterImporter(new StudentMasterReader());
    }

    public function run(string $directory, string $reportDirectory, bool $commit = false): array
    {
        if (!is_dir($directory)) {
            throw new RuntimeException('The source directory does not exist.');
        }
        if (!is_dir($reportDirectory) && !mkdir($reportDirectory, 0700, true)) {
            throw new RuntimeException('Cannot create the private report directory.');
        }
        $files = [];
        foreach (['students' => 'StudentMaster*.xlsx', 'employees' => 'Overall Faculties and Employees*.xlsx',
            'grades' => 'StudentAcademicRecords*.xlsx'] as $kind => $pattern) {
            $matches = glob(rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . $pattern);
            if (count($matches) !== 1) {
                throw new RuntimeException('Expected exactly one source matching ' . $pattern);
            }
            $files[$kind] = ['path' => $matches[0], 'name' => basename($matches[0]), 'sha256' => hash_file('sha256', $matches[0])];
        }
        $this->summary = ['mode' => $commit ? 'commit' : 'preview', 'files' => $files, 'counts' => [],
            'limitations' => ['Checks establish structural consistency, not authenticity.',
                'Inactive people remain historical records; only explicitly active teaching employees enter the faculty list.',
                'Unknown lookup codes and original grade statuses remain in source details; no login accounts or current enrollments are created.']];
        $this->issues = fopen($reportDirectory . '/issues.csv', 'w');
        $stage = tmpfile();
        if (!$this->issues || !$stage) {
            throw new RuntimeException('Cannot create validation output.');
        }
        fputcsv($this->issues, ['kind', 'source_row', 'identifier', 'reason', 'detail']);
        try {
            $reader = new StudentMasterReader(512 * 1024 * 1024);
            $students = []; $studentRows = []; $blocked = [];
            foreach ($reader->rows($files['students']['path']) as $row) {
                $this->count('students', 'rows');
                $number = $row['fields']['fldStudNo'];
                $key = $this->policy->key($number);
                $reason = $this->policy->exclusion($row['fields'], ['fldStudNo', 'fldFirstName', 'fldLastName', 'fldMiddleName'], $row['source']);
                $mapped = $this->master->map($row);
                if (!preg_match('/^[\pL\pN]+(?:-[\pL\pN]+)+$/u', $number)) {
                    $mapped['errors'][] = 'Student number contains malformed separators or unsupported characters.';
                } elseif (!preg_match('/^(?:\d{2}-\d{5}|CTP-\d{2}-\d{3})$/i', $number)) {
                    $mapped['warnings'][] = 'Unusual student number format retained without renumbering; verify with registrar.';
                }
                if (isset($studentRows[$key])) {
                    $blocked[$key] = true;
                    $this->issue('students', $row['row'], $number, 'duplicate_identity', 'All rows with this student number are withheld.');
                } elseif ($reason || $mapped['errors']) {
                    $blocked[$key] = true;
                    $this->issue('students', $row['row'], $number, $reason ?: 'invalid_identity', implode(' ', $mapped['errors']));
                }
                foreach ($mapped['warnings'] as $warning) {
                    $this->issue('students', $row['row'], $number, 'warning', $warning);
                }
                $studentRows[$key] = ['row' => $row, 'mapped' => $mapped];
            }
            $existingStudents = Schema::hasTable('students') ? DB::table('students')->get(['id', 'student_no', 'name'])->keyBy(function ($r) {
                return $this->policy->key($r->student_no);
            })->all() : [];
            $profiles = Schema::hasTable('student_profiles') ? DB::table('student_profiles')->whereNotNull('student_no')->pluck('student_id', 'student_no')->all() : [];
            $profileKeys = [];
            foreach ($profiles as $number => $id) { $profileKeys[$this->policy->key((string) $number)] = $id; }
            foreach ($studentRows as $key => $item) {
                if (isset($blocked[$key])) { continue; }
                $row = $item['row']; $mapped = $item['mapped'];
                $existing = $existingStudents[$key] ?? null;
                if (($existing && $this->policy->key($existing->name) !== $this->policy->key($mapped['student']['name']))
                    || (array_key_exists($key, $profileKeys) && (!$existing || (int) $profileKeys[$key] !== (int) $existing->id))) {
                    $blocked[$key] = true;
                    $this->issue('students', $row['row'], $mapped['student']['student_no'], 'existing_identity_conflict', 'Existing student/profile does not match; no overwrite.');
                    continue;
                }
                $students[$key] = ['number' => $mapped['student']['student_no'], 'id' => $existing ? $existing->id : null];
                $this->stage($stage, 'students', (string) $key, $row, $mapped, $existing ? 'existing' : 'ready');
            }
            unset($studentRows);
            $employees = []; $employeeNumbers = []; $employeeConflicts = [];
            foreach ($reader->rows($files['employees']['path'], ['Employee_ID', 'Employee_no', 'First_Name', 'Last_Name', 'Active', 'Is_Teaching']) as $row) {
                $this->count('employees', 'rows');
                $f = $row['fields']; $key = $this->policy->key($f['Employee_ID']);
                $numberKey = $this->policy->key($f['Employee_no']);
                if (isset($employees[$key])) { $employeeConflicts[$key] = true; }
                if (isset($employeeNumbers[$numberKey])) {
                    $employeeConflicts[$key] = true; $employeeConflicts[$employeeNumbers[$numberKey]] = true;
                }
                $employeeNumbers[$numberKey] = $key;
                $employees[$key] = $row;
            }
            $teacherNames = [];
            foreach ($employees as $key => $row) {
                $f = $row['fields'];
                $reason = $this->policy->exclusion($f, ['Employee_no', 'First_Name', 'Last_Name', 'Middle_Name', 'Employee_Name', 'Position_Code'], $row['source']);
                $middle = in_array(strtoupper($f['Middle_Name']), ['', 'NULL', 'N/A', '-'], true) ? '' : $f['Middle_Name'];
                $suffix = in_array(strtoupper($f['Suffix']), ['', 'NULL', 'N/A', '-'], true) ? '' : $f['Suffix'];
                $name = trim($f['Last_Name'] . ', ' . $f['First_Name'] . ' ' . $middle . ' ' . $suffix);
                if (isset($employeeConflicts[$key])) { $reason = 'duplicate_identity'; }
                if (!$reason && (!ctype_digit($f['Employee_ID']) || (int) $f['Employee_ID'] < 1 || !$this->validText($f['Employee_no'], 80)
                    || !$this->validText($f['First_Name'], 120) || !$this->validText($f['Last_Name'], 120)
                    || mb_strlen($name) > 255 || !in_array($f['Active'], ['0', '1'], true)
                    || !in_array($f['Is_Teaching'], ['', 'NULL', '0', '1'], true))) { $reason = 'invalid_identity_or_status'; }
                if ($reason) { $this->issue('employees', $row['row'], $f['Employee_no'], $reason); continue; }
                $teacherNames[$key] = $name;
                $mapped = ['legacy_id' => $f['Employee_ID'], 'employee_no' => $f['Employee_no'], 'name' => $name,
                    'is_active' => $f['Active'] === '1', 'is_teaching' => in_array($f['Is_Teaching'], ['0', '1'], true) ? $f['Is_Teaching'] === '1' : null];
                $this->stage($stage, 'employees', (string) $key, $row, $mapped, 'ready');
            }
            unset($employees);
            $gradeKeys = []; $gradeConflicts = [];
            foreach ($reader->rows($files['grades']['path'], ['StudentNo', 'Semester', 'Subject Name', 'Subject Description', 'Credit Units', 'Grade', 'fldStatusCode', 'Remarks', 'TeacherID']) as $row) {
                $this->count('grades', 'rows');
                $f = $row['fields']; $studentKey = $this->policy->key($f['StudentNo']);
                $reason = $this->policy->exclusion($f, ['StudentNo'], $row['source']);
                if (!$reason && !isset($students[$studentKey])) { $reason = isset($blocked[$studentKey]) ? 'excluded_student' : 'student_not_in_master'; }
                if ($reason) { $this->issue('grades', $row['row'], $f['StudentNo'], $reason); continue; }
                $mapped = $this->mapGrade($f, $students[$studentKey]['number'], $teacherNames);
                if (isset($mapped['error'])) { $this->issue('grades', $row['row'], $f['StudentNo'], 'invalid_academic_record', $mapped['error']); continue; }
                $key = $this->gradeKey($mapped);
                $digest = hash('sha256', json_encode($mapped));
                if (isset($gradeKeys[$key])) {
                    if ($gradeKeys[$key] !== $digest) {
                        $gradeConflicts[$key] = true;
                        $this->issue('grades', $row['row'], $f['StudentNo'], 'conflicting_grade', 'All rows for this student/term/subject are withheld.');
                    } else { $this->issue('grades', $row['row'], $f['StudentNo'], 'duplicate_grade'); }
                    continue;
                }
                $gradeKeys[$key] = $digest;
                $teacher = trim($f['TeacherID']);
                if (!in_array($teacher, ['', 'NULL', '0'], true) && !isset($teacherNames[$this->policy->key($teacher)])) {
                    $this->issue('grades', $row['row'], $f['StudentNo'], 'warning', 'TeacherID has no valid employee match; professor left blank.');
                }
                $this->stage($stage, 'grades', $key, $row, $mapped, 'ready');
            }
            unset($gradeKeys);
            foreach ($files as $file) {
                if (hash_file('sha256', $file['path']) !== $file['sha256']) { throw new RuntimeException('A source changed while validating. Retry with unchanged files.'); }
            }
            $this->summary['conflicting_grade_keys'] = count($gradeConflicts);
            if ($commit) {
                foreach (['students', 'student_profiles', 'student_master_sources', 'student_grade_records', 'faculties',
                    'legacy_employees', 'legacy_import_runs', 'legacy_import_sources', 'legacy_import_locks'] as $table) {
                    if (!Schema::hasTable($table)) { throw new RuntimeException('Apply migrations first. Missing table: ' . $table); }
                }
                DB::transaction(function () use ($stage, $files, $gradeConflicts) {
                    DB::table('legacy_import_locks')->where('id', 1)->lockForUpdate()->first();
                    $runId = DB::table('legacy_import_runs')->insertGetId(['manifest' => json_encode($files), 'summary' => '{}',
                        'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
                    $this->consume($stage, $files, $gradeConflicts, $runId);
                    DB::table('legacy_import_runs')->where('id', $runId)->update(['summary' => json_encode($this->summary)]);
                    $this->summary['run_id'] = $runId;
                });
            } else {
                $this->consume($stage, $files, $gradeConflicts, null);
            }
            file_put_contents($reportDirectory . '/summary.json', json_encode($this->summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
            return $this->summary;
        } finally {
            fclose($stage); fclose($this->issues);
        }
    }

    private function stage($stream, string $kind, string $key, array $row, array $mapped, string $state): void
    {
        if (fwrite($stream, json_encode(compact('kind', 'key', 'row', 'mapped', 'state'), JSON_THROW_ON_ERROR) . "\n") === false) {
            throw new RuntimeException('Unable to stage a source record.');
        }
    }

    private function mapGrade(array $f, string $number, array $teachers): array
    {
        if (!preg_match('/^(\d{4})-(\d{4})\s*\/\s*(1ST SEMESTER|2ND SEMESTER|SUMMER)$/i', $f['Semester'], $period)
            || (int) $period[2] !== (int) $period[1] + 1) { return ['error' => 'Invalid school year or semester.']; }
        if (!$this->validText($number, 80) || !$this->validText($f['Subject Name'], 60)
            || mb_strlen($f['Subject Description']) > 255 || mb_strlen($f['Remarks']) > 255) { return ['error' => 'Missing or oversized subject/student/description/remarks.']; }
        if (!is_numeric($f['Credit Units']) || (float) $f['Credit Units'] < 0 || (float) $f['Credit Units'] > 99
            || round((float) $f['Credit Units'], 1) !== (float) $f['Credit Units']) { return ['error' => 'Invalid credit units.']; }
        $grade = in_array(strtoupper($f['Grade']), ['', 'NULL'], true) ? null : $f['Grade'];
        if ($grade !== null && (!is_numeric($grade) || (float) $grade < 1 || (float) $grade > 5
            || round((float) $grade, 2) !== (float) $grade)) { return ['error' => 'Grade outside the supported 1–5 scale or precision.']; }
        $status = strtoupper($f['fldStatusCode']);
        $statuses = ['' => null, 'PSD' => 'P', 'FLD' => 'F', 'INC' => 'INC', 'OD' => 'OD', 'UD' => 'UD', 'UNOFICIALLY DROPPED' => 'UD', 'GNA' => 'GNA'];
        if (!array_key_exists($status, $statuses)) { return ['error' => 'Unknown grade status: ' . $status]; }
        if (($status === 'PSD' && ($grade === null || (float) $grade > 3)) || ($status === 'FLD' && ($grade === null || (float) $grade != 5))) {
            return ['error' => 'Grade conflicts with passed/failed status.'];
        }
        $professor = $teachers[$this->policy->key($f['TeacherID'])] ?? null;
        if ($professor && mb_strlen($professor) > 190) { $professor = null; }
        return ['student_no' => $number, 'school_year' => $period[1] . '-' . $period[2],
            'term' => ['1ST SEMESTER' => 'First', '2ND SEMESTER' => 'Second', 'SUMMER' => 'Summer'][strtoupper($period[3])],
            'subject_code' => $f['Subject Name'], 'description' => $f['Subject Description'] ?: null,
            'units' => (float) $f['Credit Units'], 'final_grade' => $grade === null ? null : (float) $grade,
            'grade_status' => $statuses[$status], 'status' => $statuses[$status], 'inc' => $status === 'INC',
            'remarks' => $f['Remarks'] ?: null, 'professor' => $professor];
    }

    private function gradeKey(array $mapped): string
    {
        $term = $this->policy->key($mapped['term']);
        if (preg_match('/^(?:first|1st)(?:\s+sem(?:ester)?)?$/', $term)) {
            $term = 'first';
        } elseif (preg_match('/^(?:second|2nd)(?:\s+sem(?:ester)?)?$/', $term)) {
            $term = 'second';
        }
        return hash('sha256', implode('|', [
            $this->policy->key($mapped['student_no']),
            $this->policy->key($mapped['school_year']),
            $term,
            $this->policy->key($mapped['subject_code']),
        ]));
    }

    private function consume($stage, array $files, array $conflicts, ?int $runId): void
    {
        rewind($stage);
        $now = date('Y-m-d H:i:s');
        $studentIds = []; $existingGrades = [];
        $profileColumns = Schema::hasTable('student_profiles') ? array_flip(Schema::getColumnListing('student_profiles')) : [];
        $studentColumns = Schema::hasTable('students') ? array_flip(Schema::getColumnListing('students')) : [];
        $snapshotColumns = [];
        foreach (['master_student_profiles', 'master_student_grade_files'] as $snapshotTable) {
            if (Schema::hasTable($snapshotTable)) { $snapshotColumns[$snapshotTable] = array_flip(Schema::getColumnListing($snapshotTable)); }
        }
        if (Schema::hasTable('student_grade_records')) {
            foreach (DB::table('student_grade_records')->select('student_no', 'school_year', 'term', 'subject_code')->cursor() as $r) {
                $existingGrades[$this->gradeKey((array) $r)] = true;
            }
        }
        $knownSources = Schema::hasTable('legacy_import_sources') ? array_fill_keys(DB::table('legacy_import_sources')->pluck('record_key')->all(), true) : [];
        while (($line = fgets($stage)) !== false) {
            $item = json_decode($line, true, 512, JSON_THROW_ON_ERROR);
            $kind = $item['kind']; $mapped = $item['mapped'];
            if ($kind === 'grades' && isset($conflicts[$item['key']])) { $this->count($kind, 'withheld_conflict'); continue; }
            $recordKey = hash('sha256', $kind . ':' . $item['key']);
            if (isset($knownSources[$recordKey])) { $this->count($kind, 'already_imported'); continue; }
            if ($kind === 'students') {
                $number = $mapped['student']['student_no'];
                $existing = Schema::hasTable('students') ? DB::table('students')->where('student_no', $number)->first() : null;
                if ($existing || $item['state'] === 'existing') {
                    if ($existing && $this->policy->key($existing->name) !== $this->policy->key($mapped['student']['name'])) {
                        throw new RuntimeException('Student identity changed after validation. Retry.');
                    }
                    $this->count($kind, 'existing');
                    $studentIds[$this->policy->key($number)] = $existing ? $existing->id : null;
                    continue;
                }
                $table = 'students'; $id = null;
                if ($runId !== null) {
                    if (DB::table('student_profiles')->where('student_no', $number)->exists()) { throw new RuntimeException('A student profile appeared after validation. Retry.'); }
                    $id = DB::table('students')->insertGetId(array_intersect_key(array_merge($mapped['student'], ['school_year' => '', 'semester' => '', 'student_type' => '', 'created_at' => $now, 'updated_at' => $now]), $studentColumns));
                    $profile = array_intersect_key($mapped['profile'], $profileColumns);
                    DB::table('student_profiles')->insert(array_merge($profile, ['student_id' => $id, 'student_no' => $number, 'created_at' => $now, 'updated_at' => $now]));
                    DB::table('student_master_sources')->insert(['student_id' => $id, 'is_active' => $mapped['active'], 'student_type_code' => $item['row']['fields']['fldStudType'] ?? null,
                        'source_row' => $item['row']['row'], 'file_sha256' => $files[$kind]['sha256'], 'source_data' => json_encode($item['row']['source'], JSON_THROW_ON_ERROR), 'created_at' => $now, 'updated_at' => $now]);
                    foreach ($snapshotColumns as $snapshotTable => $columns) {
                        if (!DB::table($snapshotTable)->where('student_no', $number)->exists() && mb_strlen($mapped['student']['name']) <= 190) {
                            DB::table($snapshotTable)->insert(array_intersect_key(['student_no' => $number, 'source_student_id' => $id,
                                'student_name' => $mapped['student']['name'], 'course' => 'Not Set', 'year_level' => 'Not Set',
                                'snapshot_taken_at' => $now, 'snapshot_note' => 'Legacy StudentMaster import; academic program/year level not supplied.',
                                'is_snapshot' => true, 'created_at' => $now, 'updated_at' => $now], $columns));
                        }
                    }
                }
                $studentIds[$this->policy->key($number)] = $id;
            } elseif ($kind === 'employees') {
                $table = 'legacy_employees'; $id = null;
                if (Schema::hasTable($table) && DB::table($table)->where(function ($q) use ($mapped) {
                    $q->where('legacy_id', $mapped['legacy_id'])->orWhere('employee_no', $mapped['employee_no']);
                })->exists()) { $this->issue($kind, $item['row']['row'], $mapped['employee_no'], 'existing_employee', 'No overwrite.'); continue; }
                $facultyId = null; $faculty = null;
                $employment = ['PART' => 'Part-time Teacher', 'PERM' => 'Full-time Teacher', 'REG' => 'Full-time Teacher'][$item['row']['fields']['Employment_Status_Code'] ?? ''] ?? null;
                $makeFaculty = $mapped['is_teaching'] === true && $mapped['is_active'] && $employment !== null;
                if ($makeFaculty && Schema::hasTable('faculties')) {
                    $faculty = DB::table('faculties')->where('code', $mapped['employee_no'])->first();
                    if ($faculty && $this->policy->key($faculty->name) !== $this->policy->key($mapped['name'])) {
                        $this->issue($kind, $item['row']['row'], $mapped['employee_no'], 'existing_faculty_conflict'); continue;
                    }
                }
                if ($mapped['is_teaching'] === true && $mapped['is_active'] && $employment === null) {
                    $this->issue($kind, $item['row']['row'], $mapped['employee_no'], 'warning', 'Employment code requires review before adding to scheduling faculty. Employee retained.');
                }
                if ($makeFaculty) { $this->count($kind, $runId !== null ? 'faculty_linked' : 'faculty_ready'); }
                if ($runId !== null) {
                    if ($makeFaculty) {
                        $facultyPayload = ['code' => $mapped['employee_no'], 'name' => $mapped['name'], 'employment_type' => $employment, 'created_at' => $now, 'updated_at' => $now];
                        $maximum = $item['row']['fields']['fldMaxUnits'] ?? '';
                        if (is_numeric($maximum) && (float) $maximum >= 0 && (float) $maximum <= 999.99) { $facultyPayload['max_load_units'] = (float) $maximum; }
                        $facultyId = $faculty ? $faculty->id : DB::table('faculties')->insertGetId($facultyPayload);
                    }
                    $id = DB::table($table)->insertGetId(array_merge($mapped, ['faculty_id' => $facultyId, 'created_at' => $now, 'updated_at' => $now]));
                }
            } else {
                if (isset($existingGrades[$item['key']])) { $this->issue($kind, $item['row']['row'], $mapped['student_no'], 'existing_grade', 'Existing academic record retained without overwrite.'); continue; }
                $table = 'student_grade_records'; $id = null;
                if ($runId !== null) {
                    $numberKey = $this->policy->key($mapped['student_no']);
                    if (!isset($studentIds[$numberKey])) { $studentIds[$numberKey] = DB::table('students')->where('student_no', $mapped['student_no'])->value('id'); }
                    if (!$studentIds[$numberKey]) { throw new RuntimeException('Student identity unavailable at commit.'); }
                    $id = DB::table($table)->insertGetId(array_merge($mapped, ['student_id' => $studentIds[$numberKey], 'created_at' => $now, 'updated_at' => $now]));
                }
            }
            $this->count($kind, $runId !== null ? 'created' : 'ready');
            if ($runId !== null) {
                DB::table('legacy_import_sources')->insert(['run_id' => $runId, 'kind' => $kind, 'record_key' => $recordKey,
                    'target_table' => $table, 'target_id' => $id, 'source_file' => $files[$kind]['name'], 'file_sha256' => $files[$kind]['sha256'],
                    'source_row' => $item['row']['row'], 'source_data' => json_encode($item['row']['source'], JSON_THROW_ON_ERROR), 'created_at' => $now, 'updated_at' => $now]);
            }
        }
    }

    private function validText(string $value, int $limit): bool
    {
        return $value !== '' && mb_strlen($value) <= $limit && !preg_match('/[\x00-\x1F]/', $value);
    }

    private function count(string $kind, string $state): void
    {
        $this->summary['counts'][$kind][$state] = ($this->summary['counts'][$kind][$state] ?? 0) + 1;
    }

    private function issue(string $kind, int $row, string $identifier, string $reason, string $detail = ''): void
    {
        $this->count($kind, $reason);
        // Prevent formula execution when the CSV is opened in Excel.
        $values = array_map(function ($v) { return preg_match('/^[=+@\-\t\r]/', (string) $v) ? "'" . $v : $v; }, [$kind, $row, $identifier, $reason, $detail]);
        fputcsv($this->issues, $values);
    }
}
