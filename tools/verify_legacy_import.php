<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

if (DB::connection()->getDriverName() !== 'mysql') {
    throw new RuntimeException('Verification requires MySQL.');
}

$result = ['driver' => 'mysql', 'records' => [], 'checks' => [], 'issues' => []];
$check = function (string $name, bool $passed) use (&$result): void {
    $result['checks'][$name] = $passed;
    if (!$passed) { $result['issues'][] = $name; }
};
$policy = new App\Services\LegacyImportPolicy();
$fields = function (string $json): array {
    $values = [];
    foreach (json_decode($json, true, 512, JSON_THROW_ON_ERROR) as $cell) {
        if (!isset($values[$cell['heading']]) || $values[$cell['heading']] === '') {
            $values[$cell['heading']] = $cell['value'];
        }
    }
    return $values;
};

foreach (['students' => 'students', 'employees' => 'legacy_employees', 'grades' => 'student_grade_records'] as $kind => $table) {
    $base = DB::table('legacy_import_sources as l')->where('l.kind', $kind);
    $result['records'][$kind] = (clone $base)->count();
    $missing = (clone $base)->leftJoin($table . ' as t', 't.id', '=', 'l.target_id')->whereNull('t.id')->count();
    $check($kind . '_source_targets_exist', $missing === 0);
}

$studentErrors = 0;
foreach (DB::table('legacy_import_sources as l')->join('students as s', 's.id', '=', 'l.target_id')
    ->where('l.kind', 'students')->select('l.source_data', 's.id', 's.student_no', 's.name')->cursor() as $row) {
    $source = $fields($row->source_data);
    if ($policy->key($row->student_no) !== $policy->key($source['fldStudNo'])
        || $policy->exclusion($source, ['fldStudNo', 'fldFirstName', 'fldLastName', 'fldMiddleName']) !== null) {
        $studentErrors++;
    }
}
$check('imported_student_identities_valid_and_not_cancelled', $studentErrors === 0);

foreach (['student_profiles', 'student_master_sources', 'master_student_profiles', 'master_student_grade_files'] as $table) {
    $column = strpos($table, 'master_student_') === 0 ? 'source_student_id' : 'student_id';
    $missing = DB::table('legacy_import_sources as l')->leftJoin($table . ' as t', 't.' . $column, '=', 'l.target_id')
        ->where('l.kind', 'students')->whereNull('t.id')->count();
    $check('imported_students_have_' . $table, $missing === 0);
}

$orphans = DB::table('legacy_import_sources as l')->join('student_grade_records as g', 'g.id', '=', 'l.target_id')
    ->leftJoin('students as s', 's.id', '=', 'g.student_id')->where('l.kind', 'grades')
    ->where(function ($q) { $q->whereNull('s.id')->orWhereRaw('LOWER(TRIM(s.student_no)) <> LOWER(TRIM(g.student_no))'); })->count();
$check('all_imported_grades_link_to_correct_students', $orphans === 0);

$gradeErrors = 0; $duplicates = 0; $seen = [];
$statuses = ['' => null, 'PSD' => 'P', 'FLD' => 'F', 'INC' => 'INC', 'OD' => 'OD', 'UD' => 'UD', 'UNOFICIALLY DROPPED' => 'UD', 'GNA' => 'GNA'];
foreach (DB::table('legacy_import_sources as l')->join('student_grade_records as g', 'g.id', '=', 'l.target_id')
    ->where('l.kind', 'grades')->select('l.source_data', 'g.*')->cursor() as $row) {
    $source = $fields($row->source_data);
    $expectedGrade = in_array(strtoupper($source['Grade']), ['', 'NULL'], true) ? null : (float) $source['Grade'];
    $status = strtoupper($source['fldStatusCode']);
    preg_match('/^(\d{4}-\d{4})\s*\/\s*(1ST SEMESTER|2ND SEMESTER|SUMMER)$/i', $source['Semester'], $period);
    $expectedTerm = ['1ST SEMESTER' => 'First', '2ND SEMESTER' => 'Second', 'SUMMER' => 'Summer'][strtoupper($period[2])];
    if ($row->student_no !== $source['StudentNo'] || $row->subject_code !== $source['Subject Name']
        || $row->school_year !== $period[1] || $row->term !== $expectedTerm
        || (float) $row->units !== (float) $source['Credit Units']
        || ($expectedGrade === null ? $row->final_grade !== null : $row->final_grade === null || abs((float) $row->final_grade - $expectedGrade) > 0.00001)
        || $row->grade_status !== $statuses[$status] || (bool) $row->inc !== ($status === 'INC')
        || ($row->description ?? '') !== $source['Subject Description'] || ($row->remarks ?? '') !== $source['Remarks']) {
        $gradeErrors++;
    }
    $key = $policy->key(implode('|', [$row->student_no, $row->school_year, $row->term, $row->subject_code]));
    if (isset($seen[$key])) { $duplicates++; }
    $seen[$key] = true;
}
$check('all_imported_grade_values_match_source', $gradeErrors === 0);
$check('no_duplicate_imported_student_term_subject_records', $duplicates === 0);
unset($seen);

$badFacultyLinks = DB::table('legacy_employees as e')->leftJoin('faculties as f', 'f.id', '=', 'e.faculty_id')
    ->whereNotNull('e.faculty_id')->where(function ($q) {
        $q->whereNull('f.id')->orWhere('e.is_active', false)->orWhere('e.is_teaching', false)->orWhereNull('e.is_teaching')
            ->orWhereRaw('LOWER(TRIM(e.employee_no)) <> LOWER(TRIM(f.code))');
    })->count();
$check('employee_faculty_links_are_valid_active_teachers', $badFacultyLinks === 0);

$sampleId = DB::table('legacy_import_sources as l')->join('student_grade_records as g', 'g.id', '=', 'l.target_id')
    ->where('l.kind', 'grades')->value('g.student_id');
$visible = $sampleId ? (new App\Services\HistoricalStudentGrades())->merge(App\Student::findOrFail($sampleId), collect())->count() : 0;
$check('historical_grade_screen_data_loads', $visible > 0);

$result['passed'] = !$result['issues'];
$path = storage_path('app/import-reports/mysql-integrity-verification.json');
file_put_contents($path, json_encode($result, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
echo json_encode($result, JSON_PRETTY_PRINT), PHP_EOL;
echo 'Report: ' . $path, PHP_EOL;
exit($result['passed'] ? 0 : 1);
