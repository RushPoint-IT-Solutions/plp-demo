<?php

namespace App\Http\Controllers\Registrar\Services;

use App\AcademicTerm;
use App\Http\Controllers\Controller;
use App\Student;
use App\Subject;
use App\SystemSchoolSemester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class ClassListController extends Controller
{
    public function index(Request $request)
    {
        $state = $this->resolveState($request);

        $classRows = $this->buildSubjectQuery($state['academic_term_ids'], $state['has_term_filter'], $state['search'])
            ->paginate(25)
            ->appends($request->except('page'));

        $selectedSubject = null;
        $sectionStudents = null;

        if ($state['subject_id'] > 0) {
            $selectedSubject = Subject::query()
                ->with(['academicTerm', 'canonicalCourse', 'facultyModel'])
                ->find($state['subject_id']);

            if ($selectedSubject && $state['has_term_filter']) {
                $selectedTermId = (int) $selectedSubject->academic_term_id;
                if (!in_array($selectedTermId, $state['academic_term_ids'], true)) {
                    $selectedSubject = null;
                }
            }

            if ($selectedSubject) {
                $sectionStudents = $this->buildSectionStudentsQuery((int) $selectedSubject->id, $state['search'])
                    ->paginate(25, ['*'], 'detail_page')
                    ->appends($request->except('detail_page'));
            }
        }

        $queryBase = $this->cleanQuery([
            'q' => $state['search'],
            'school_year' => $state['selected_school_year'],
            'semester' => $state['selected_semester'],
        ]);

        return view('registrar.services.classroom-faculty.class-list', [
            'classRows' => $classRows,
            'selectedSubject' => $selectedSubject,
            'sectionStudents' => $sectionStudents,
            'search' => $state['search'],
            'selectedSchoolYear' => $state['selected_school_year'],
            'selectedSemester' => $state['selected_semester'],
            'schoolYearOptions' => $state['school_year_options'],
            'semesterOptions' => $state['semester_options'],
            'queryBase' => $queryBase,
        ]);
    }

    public function export(Request $request, $format)
    {
        $format = strtolower((string) $format);
        if (!in_array($format, ['pdf', 'excel'], true)) {
            abort(404);
        }

        $state = $this->resolveState($request);

        $subjects = $this->buildSubjectQuery($state['academic_term_ids'], $state['has_term_filter'], $state['search'])->get();

        $selectedSubject = null;
        $sectionStudents = collect();

        if ($state['subject_id'] > 0) {
            $selectedSubject = Subject::query()
                ->with(['academicTerm', 'canonicalCourse', 'facultyModel'])
                ->find($state['subject_id']);

            if ($selectedSubject && $state['has_term_filter']) {
                $selectedTermId = (int) $selectedSubject->academic_term_id;
                if (!in_array($selectedTermId, $state['academic_term_ids'], true)) {
                    $selectedSubject = null;
                }
            }

            if ($selectedSubject) {
                $sectionStudents = $this->buildSectionStudentsQuery((int) $selectedSubject->id, $state['search'])->get();
            }
        }

        if ($format === 'excel') {
            return $this->makeCsvDownloadResponse($subjects, $selectedSubject, $sectionStudents, $state);
        }

        $filename = $selectedSubject
            ? 'class-list-' . $this->sanitizeFilenameSegment($selectedSubject->code) . '-section.pdf'
            : 'class-list-summary.pdf';

        $html = view('registrar.services.classroom-faculty.exports.class-list-pdf', [
            'subjects' => $subjects,
            'selectedSubject' => $selectedSubject,
            'sectionStudents' => $sectionStudents,
            'state' => $state,
            'controller' => $this,
        ])->render();

        return $this->makePdfDownloadResponse($html, $filename, 'L');
    }

    public function sectionLabel(Subject $subject)
    {
        $courseCode = trim((string) optional($subject->canonicalCourse)->code);
        $yearSection = trim((string) $subject->year_section);

        return trim($courseCode . ' ' . $yearSection) !== ''
            ? trim($courseCode . ' ' . $yearSection)
            : 'N/A';
    }

    public function scheduleLabel(Subject $subject)
    {
        $parts = [];

        $days = strtoupper(str_replace(',', '/', trim((string) $subject->days)));
        if ($days !== '') {
            $parts[] = $days;
        }

        $time = trim((string) $subject->formatted_time);
        if ($time !== '') {
            $parts[] = $time;
        }

        $room = trim((string) $subject->room);
        if ($room !== '') {
            $parts[] = 'Room#' . $room;
        }

        return count($parts) ? implode(' | ', $parts) : 'TBA';
    }

    private function resolveState(Request $request)
    {
        list($schoolYears, $semesterMap) = $this->configuredSchoolSemesterOptions();
        list($defaultSchoolYear, $defaultSemester) = $this->defaultSelection($schoolYears, $semesterMap);

        $selectedSchoolYear = trim((string) $request->query('school_year', $defaultSchoolYear));
        if ($selectedSchoolYear !== '' && !in_array($selectedSchoolYear, $schoolYears, true)) {
            $selectedSchoolYear = '';
        }

        $semesterOptions = $this->semesterOptions($semesterMap, $selectedSchoolYear);

        $selectedSemester = $this->normalizeSemesterValue($request->query('semester', $defaultSemester));
        if ($selectedSemester !== '' && !$this->optionContainsValue($semesterOptions, $selectedSemester)) {
            $selectedSemester = '';
        }

        $search = trim((string) $request->query('q', ''));
        $subjectId = (int) $request->query('subject_id', 0);

        $hasTermFilter = $selectedSchoolYear !== '' || $selectedSemester !== '';
        $academicTermIds = $this->resolveAcademicTermIds($selectedSchoolYear, $selectedSemester, $hasTermFilter);

        return [
            'search' => $search,
            'selected_school_year' => $selectedSchoolYear,
            'selected_semester' => $selectedSemester,
            'school_year_options' => $this->schoolYearOptions($schoolYears),
            'semester_options' => $semesterOptions,
            'subject_id' => $subjectId,
            'has_term_filter' => $hasTermFilter,
            'academic_term_ids' => $academicTermIds,
        ];
    }

    private function buildSubjectQuery(array $academicTermIds, $hasTermFilter, $search)
    {
        return Subject::query()
            ->with(['academicTerm', 'canonicalCourse', 'facultyModel'])
            ->when($hasTermFilter, function ($query) use ($academicTermIds) {
                if (count($academicTermIds)) {
                    $query->whereIn('subjects.academic_term_id', $academicTermIds);
                    return;
                }

                $query->whereRaw('1 = 0');
            })
            ->when($search !== '', function ($query) use ($search) {
                $like = '%' . $search . '%';

                $query->where(function ($inner) use ($like) {
                    $inner->where('subjects.code', 'like', $like)
                        ->orWhere('subjects.name', 'like', $like)
                        ->orWhere('subjects.year_section', 'like', $like)
                        ->orWhere('subjects.room', 'like', $like)
                        ->orWhereHas('canonicalCourse', function ($courseQuery) use ($like) {
                            $courseQuery->where('code', 'like', $like)
                                ->orWhere('name', 'like', $like);
                        })
                        ->orWhereHas('facultyModel', function ($facultyQuery) use ($like) {
                            $facultyQuery->where('code', 'like', $like)
                                ->orWhere('name', 'like', $like);
                        });
                });
            })
            ->orderByRaw('CASE WHEN subjects.course_id IS NULL THEN 1 ELSE 0 END')
            ->orderBy('subjects.course_id')
            ->orderByRaw('COALESCE(subjects.year_section, "")')
            ->orderBy('subjects.code');
    }

    private function buildSectionStudentsQuery($subjectId, $search)
    {
        return Student::query()
            ->with(['canonicalCourse', 'yearBlock'])
            ->whereHas('subjects', function ($subjectQuery) use ($subjectId) {
                $subjectQuery->where('subjects.id', $subjectId);
            })
            ->when($search !== '', function ($query) use ($search) {
                $like = '%' . $search . '%';

                $query->where(function ($inner) use ($like) {
                    $inner->where('students.student_no', 'like', $like)
                        ->orWhere('students.name', 'like', $like)
                        ->orWhereHas('canonicalCourse', function ($courseQuery) use ($like) {
                            $courseQuery->where('code', 'like', $like)
                                ->orWhere('name', 'like', $like);
                        })
                        ->orWhereHas('yearBlock', function ($yearQuery) use ($like) {
                            $yearQuery->where('label', 'like', $like);
                        });
                });
            })
            ->orderBy('students.name');
    }

    private function configuredSchoolSemesterOptions()
    {
        $schoolYears = [];
        $semesterMap = [];

        if (Schema::hasTable('system_school_semesters')) {
            $this->ensureSchoolSemesterRowsExist();

            $hasLegacySchoolYear = Schema::hasColumn('system_school_semesters', 'school_year');
            $hasLegacySemester = Schema::hasColumn('system_school_semesters', 'semester');
            $hasAcademicTermId = Schema::hasColumn('system_school_semesters', 'academic_term_id');

            $configRowsQuery = SystemSchoolSemester::query()->orderByDesc('id');

            if ($hasLegacySchoolYear) {
                $configRowsQuery->orderBy('school_year', 'desc');
            }

            if ($hasAcademicTermId) {
                $configRowsQuery->with('academicTerm');
            }

            $configRows = $configRowsQuery->get();

            foreach ($configRows as $configRow) {
                $schoolYear = '';
                if ($hasLegacySchoolYear) {
                    $schoolYear = trim((string) $configRow->getAttribute('school_year'));
                }

                if ($schoolYear === '' && $configRow->relationLoaded('academicTerm') && $configRow->academicTerm) {
                    $schoolYear = trim((string) $configRow->academicTerm->school_year);
                }

                $rawSemester = '';
                if ($hasLegacySemester) {
                    $rawSemester = trim((string) $configRow->getAttribute('semester'));
                }

                if ($rawSemester === '' && $configRow->relationLoaded('academicTerm') && $configRow->academicTerm) {
                    $rawSemester = trim((string) $configRow->academicTerm->term);
                }

                $semester = $this->normalizeSemesterValue($rawSemester);

                if ($schoolYear === '' || $semester === '') {
                    continue;
                }

                if (!array_key_exists($schoolYear, $schoolYears)) {
                    $schoolYears[$schoolYear] = $schoolYear;
                }

                if (!array_key_exists($schoolYear, $semesterMap)) {
                    $semesterMap[$schoolYear] = [];
                }

                if (!in_array($semester, $semesterMap[$schoolYear], true)) {
                    $semesterMap[$schoolYear][] = $semester;
                }
            }
        }

        $terms = AcademicTerm::query()
            ->orderBy('school_year', 'desc')
            ->orderByRaw($this->termSortSql())
            ->orderBy('term')
            ->get(['school_year', 'term']);

        foreach ($terms as $term) {
            $schoolYear = trim((string) $term->school_year);
            $semester = $this->normalizeSemesterValue($term->term);

            if ($schoolYear === '' || $semester === '') {
                continue;
            }

            if (!array_key_exists($schoolYear, $schoolYears)) {
                $schoolYears[$schoolYear] = $schoolYear;
            }

            if (!array_key_exists($schoolYear, $semesterMap)) {
                $semesterMap[$schoolYear] = [];
            }

            if (!in_array($semester, $semesterMap[$schoolYear], true)) {
                $semesterMap[$schoolYear][] = $semester;
            }
        }

        foreach ($semesterMap as $schoolYear => $semesters) {
            usort($semesters, function ($left, $right) {
                return $this->semesterWeight($left) <=> $this->semesterWeight($right);
            });

            $semesterMap[$schoolYear] = array_values(array_unique($semesters));
        }

        return [array_values($schoolYears), $semesterMap];
    }

    private function ensureSchoolSemesterRowsExist()
    {
        if (!Schema::hasTable('system_school_semesters')) {
            return;
        }

        if (SystemSchoolSemester::query()->exists()) {
            return;
        }

        $yearStart = (int) date('Y');
        $schoolYear = $yearStart . '-' . ($yearStart + 1);
        $semester = 'First';

        $payload = [];

        if (Schema::hasColumn('system_school_semesters', 'school_year')) {
            $payload['school_year'] = $schoolYear;
        }

        if (Schema::hasColumn('system_school_semesters', 'semester')) {
            $payload['semester'] = $semester;
        }

        if (Schema::hasColumn('system_school_semesters', 'academic_term_id')) {
            $academicTerm = AcademicTerm::firstOrCreate(
                ['canonical_key' => strtolower($schoolYear . '|' . $semester)],
                ['school_year' => $schoolYear, 'term' => $semester]
            );

            $payload['academic_term_id'] = (int) $academicTerm->id;
        }

        if (!count($payload)) {
            return;
        }

        SystemSchoolSemester::query()->create($payload);
    }

    private function defaultSelection(array $schoolYears, array $semesterMap)
    {
        $defaultSchoolYear = count($schoolYears) ? (string) $schoolYears[0] : '';
        $defaultSemester = '';

        if ($defaultSchoolYear !== '' && array_key_exists($defaultSchoolYear, $semesterMap) && count($semesterMap[$defaultSchoolYear])) {
            $defaultSemester = (string) $semesterMap[$defaultSchoolYear][0];
        }

        return [$defaultSchoolYear, $defaultSemester];
    }

    private function schoolYearOptions(array $schoolYears)
    {
        $options = [
            ['value' => '', 'label' => 'All School Years'],
        ];

        foreach ($schoolYears as $schoolYear) {
            $options[] = [
                'value' => (string) $schoolYear,
                'label' => (string) $schoolYear,
            ];
        }

        return $options;
    }

    private function semesterOptions(array $semesterMap, $selectedSchoolYear)
    {
        $options = [
            ['value' => '', 'label' => 'All Semesters'],
        ];

        $semesters = [];
        $selectedSchoolYear = trim((string) $selectedSchoolYear);

        if ($selectedSchoolYear !== '' && array_key_exists($selectedSchoolYear, $semesterMap)) {
            $semesters = $semesterMap[$selectedSchoolYear];
        }

        if (!count($semesters)) {
            foreach ($semesterMap as $yearSemesters) {
                foreach ($yearSemesters as $semester) {
                    if (!in_array($semester, $semesters, true)) {
                        $semesters[] = $semester;
                    }
                }
            }
        }

        usort($semesters, function ($left, $right) {
            return $this->semesterWeight($left) <=> $this->semesterWeight($right);
        });

        foreach ($semesters as $semester) {
            $options[] = [
                'value' => (string) $semester,
                'label' => (string) $semester,
            ];
        }

        return $options;
    }

    private function optionContainsValue(array $options, $value)
    {
        foreach ($options as $option) {
            if ((string) $option['value'] === (string) $value) {
                return true;
            }
        }

        return false;
    }

    private function resolveAcademicTermIds($selectedSchoolYear, $selectedSemester, $hasTermFilter)
    {
        if (!$hasTermFilter) {
            return [];
        }

        $termsQuery = AcademicTerm::query()->select(['id', 'school_year', 'term']);

        if (trim((string) $selectedSchoolYear) !== '') {
            $termsQuery->where('school_year', (string) $selectedSchoolYear);
        }

        $terms = $termsQuery->get();

        if (trim((string) $selectedSemester) !== '') {
            $selectedSemester = (string) $selectedSemester;

            $terms = $terms->filter(function ($term) use ($selectedSemester) {
                return $this->normalizeSemesterValue($term->term) === $selectedSemester;
            });
        }

        return $terms->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->values()
            ->all();
    }

    private function normalizeSemesterValue($value)
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

        return '';
    }

    private function semesterWeight($semester)
    {
        $normalized = $this->normalizeSemesterValue($semester);
        if ($normalized === 'First') {
            return 1;
        }

        if ($normalized === 'Second') {
            return 2;
        }

        if ($normalized === 'Summer') {
            return 3;
        }

        return 4;
    }

    private function systemSemesterSortSql()
    {
        return "CASE
            WHEN LOWER(semester) LIKE '%first%' OR LOWER(semester) LIKE '%1st%' THEN 1
            WHEN LOWER(semester) LIKE '%second%' OR LOWER(semester) LIKE '%2nd%' THEN 2
            WHEN LOWER(semester) LIKE '%summer%' THEN 3
            ELSE 4
        END";
    }

    private function makeCsvDownloadResponse($subjects, $selectedSubject, $sectionStudents, array $state)
    {
        $handle = fopen('php://temp', 'w+');

        if ($selectedSubject) {
            fputcsv($handle, ['Class List Report']);
            fputcsv($handle, ['Section', $this->sectionLabel($selectedSubject)]);
            fputcsv($handle, ['Subject', $selectedSubject->code . ' - ' . $selectedSubject->name]);
            fputcsv($handle, ['Schedule', $this->scheduleLabel($selectedSubject)]);
            fputcsv($handle, ['Professor', (string) optional($selectedSubject->facultyModel)->name ?: 'TBA']);
            fputcsv($handle, []);
            fputcsv($handle, ['#', 'Student No.', 'Name', 'Course', 'Year Level']);

            foreach ($sectionStudents->values() as $index => $student) {
                fputcsv($handle, [
                    $index + 1,
                    (string) $student->student_no,
                    (string) $student->name,
                    (string) optional($student->canonicalCourse)->name,
                    (string) optional($student->yearBlock)->label,
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Total Students', $sectionStudents->count()]);
        } else {
            fputcsv($handle, ['Class List Report']);
            fputcsv($handle, ['School Year', $state['selected_school_year'] !== '' ? $state['selected_school_year'] : 'All']);
            fputcsv($handle, ['Semester', $state['selected_semester'] !== '' ? $state['selected_semester'] : 'All']);
            fputcsv($handle, []);
            fputcsv($handle, ['#', 'Section', 'Subject Code', 'Description', 'Schedule']);

            foreach ($subjects->values() as $index => $subject) {
                fputcsv($handle, [
                    $index + 1,
                    $this->sectionLabel($subject),
                    (string) $subject->code,
                    (string) $subject->name,
                    $this->scheduleLabel($subject),
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Total Subjects', $subjects->count()]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        $filename = $selectedSubject
            ? 'class-list-' . $this->sanitizeFilenameSegment($selectedSubject->code) . '-section.csv'
            : 'class-list-summary.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function makePdfDownloadResponse($html, $filename, $orientation)
    {
        $format = strtoupper((string) $orientation) === 'L' ? 'A4-L' : 'A4';

        $pdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => $format,
            'tempDir' => $this->resolveWritableMpdfTempDir(),
            'margin_top' => 8,
            'margin_right' => 8,
            'margin_bottom' => 8,
            'margin_left' => 8,
        ]);

        $pdf->WriteHTML((string) $html);
        $binary = $pdf->Output((string) $filename, Destination::STRING_RETURN);

        return response($binary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function resolveWritableMpdfTempDir()
    {
        $candidates = [
            storage_path('framework/cache/mpdf-temp'),
            storage_path('app/mpdf-temp'),
            rtrim((string) sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'plp-demo-mpdf-temp',
        ];

        foreach ($candidates as $candidate) {
            if (!is_dir($candidate)) {
                @mkdir($candidate, 0775, true);
            }

            if (is_dir($candidate) && is_writable($candidate)) {
                return $candidate;
            }
        }

        return rtrim((string) sys_get_temp_dir(), DIRECTORY_SEPARATOR);
    }

    private function sanitizeFilenameSegment($value)
    {
        $normalized = strtolower(trim((string) $value));
        $normalized = preg_replace('/[^a-z0-9]+/', '-', $normalized);
        $normalized = trim((string) $normalized, '-');

        return $normalized !== '' ? $normalized : 'class-list';
    }

    private function termSortSql()
    {
        return "CASE
            WHEN LOWER(term) LIKE '%first%' OR LOWER(term) LIKE '1st%' THEN 1
            WHEN LOWER(term) LIKE '%second%' OR LOWER(term) LIKE '2nd%' THEN 2
            WHEN LOWER(term) LIKE '%summer%' THEN 3
            ELSE 4
        END";
    }

    private function cleanQuery(array $query)
    {
        return array_filter($query, function ($value) {
            if (is_int($value)) {
                return $value > 0;
            }

            return !(is_null($value) || $value === '');
        });
    }
}
