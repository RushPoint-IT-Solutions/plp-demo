<?php

namespace App\Http\Controllers\Registrar\Services;

use App\AcademicTerm;
use App\AttendanceRecord;
use App\Http\Controllers\Controller;
use App\Student;
use App\Subject;
use App\Support\SystemConfigSchoolTermOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    private $statuses = ['Present', 'Absent', 'Late', 'Excused'];

    public function index(Request $request)
    {
        $state = $this->resolveState($request);

        $classRows = $this->buildSubjectQuery($state['academic_term_ids'], $state['has_term_filter'], $state['search'])
            ->paginate(10)
            ->appends($request->except('page'));

        return view('registrar.services.classroom-faculty.attendance', [
            'classRows' => $classRows,
            'search' => $state['search'],
            'selectedSchoolYear' => $state['selected_school_year'],
            'selectedSemester' => $state['selected_semester'],
            'schoolYearOptions' => $state['school_year_options'],
            'semesterOptions' => $state['semester_options'],
            'semesterMap' => $state['semester_map'],
            'statuses' => $this->statuses,
            'queryBase' => $this->cleanQuery([
                'q' => $state['search'],
                'school_year' => $state['selected_school_year'],
                'semester' => $state['selected_semester'],
            ]),
        ]);
    }

    public function students(Request $request, Subject $subject)
    {
        $date = $this->normalizeDate($request->query('date'));

        $existing = AttendanceRecord::query()
            ->where('subject_id', $subject->id)
            ->where('attendance_date', $date)
            ->get()
            ->keyBy('student_id');

        $students = $subject->students()
            ->with('canonicalCourse')
            ->orderBy('name')
            ->get()
            ->map(function (Student $student) use ($existing) {
                $record = $existing->get($student->id);

                return [
                    'id' => (int) $student->id,
                    'student_no' => (string) $student->student_no,
                    'name' => (string) $student->name,
                    'status' => $record ? $record->status : 'Present',
                ];
            })
            ->values();

        return response()->json([
            'date' => $date,
            'section' => trim((string) optional($subject->canonicalCourse)->code . ' ' . (string) $subject->year_section),
            'subject' => trim((string) $subject->code . ' - ' . (string) $subject->name),
            'students' => $students,
            'summary' => $this->summaryFor($subject),
        ]);
    }

    public function store(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'records' => ['required', 'array', 'min:1'],
            'records.*.student_id' => ['required', 'integer'],
            'records.*.status' => ['required', 'string', 'in:' . implode(',', $this->statuses)],
        ]);

        $date = $this->normalizeDate($validated['date']);
        $enrolledIds = $subject->students()->pluck('students.id')->all();

        $userId = auth()->id();
        $saved = 0;

        foreach ($validated['records'] as $row) {
            $studentId = (int) $row['student_id'];
            if (!in_array($studentId, $enrolledIds, true)) {
                continue;
            }

            AttendanceRecord::query()->updateOrCreate(
                [
                    'subject_id' => $subject->id,
                    'student_id' => $studentId,
                    'attendance_date' => $date,
                ],
                [
                    'status' => $row['status'],
                    'recorded_by_user_id' => $userId,
                ]
            );
            $saved++;
        }

        return response()->json([
            'ok' => true,
            'message' => 'Attendance saved for ' . $saved . ' student(s) on ' . $date . '.',
            'date' => $date,
            'summary' => $this->summaryFor($subject),
        ]);
    }

    public function import(Request $request, Subject $subject)
    {
        $request->validate([
            'file' => ['required', 'file'],
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return response()->json(['ok' => false, 'message' => 'Unable to read the uploaded file.'], 422);
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return response()->json(['ok' => false, 'message' => 'The file is empty.'], 422);
        }

        $header = array_map(function ($col) {
            return strtolower(trim((string) $col));
        }, $header);

        $studentNoIdx = array_search('student no', $header, true);
        if ($studentNoIdx === false) {
            $studentNoIdx = array_search('student_no', $header, true);
        }
        $dateIdx = array_search('date', $header, true);
        $statusIdx = array_search('status', $header, true);

        if ($studentNoIdx === false || $dateIdx === false || $statusIdx === false) {
            fclose($handle);
            return response()->json([
                'ok' => false,
                'message' => 'CSV must have columns: Student No, Date, Status.',
            ], 422);
        }

        $studentsByNo = $subject->students()->get()->keyBy(function (Student $student) {
            return strtolower(trim((string) $student->student_no));
        });

        $imported = 0;
        $errors = [];
        $rowNumber = 1;
        $userId = auth()->id();
        $dryRun = (bool) $request->boolean('dry_run');

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            if (count(array_filter($row, function ($v) { return trim((string) $v) !== ''; })) === 0) {
                continue;
            }

            $studentNo = strtolower(trim((string) ($row[$studentNoIdx] ?? '')));
            $dateValue = trim((string) ($row[$dateIdx] ?? ''));
            $statusValue = trim((string) ($row[$statusIdx] ?? ''));
            $statusMatch = collect($this->statuses)->first(function ($status) use ($statusValue) {
                return strcasecmp($status, $statusValue) === 0;
            });

            if ($studentNo === '' || !$studentsByNo->has($studentNo)) {
                $errors[] = 'Row ' . $rowNumber . ': student "' . $row[$studentNoIdx] . '" is not enrolled in this section.';
                continue;
            }

            $date = $this->normalizeDate($dateValue);
            if (!$date) {
                $errors[] = 'Row ' . $rowNumber . ': invalid date "' . $dateValue . '".';
                continue;
            }

            if (!$statusMatch) {
                $errors[] = 'Row ' . $rowNumber . ': invalid status "' . $statusValue . '" (expected ' . implode('/', $this->statuses) . ').';
                continue;
            }

            if (!$dryRun) {
                AttendanceRecord::query()->updateOrCreate(
                    [
                        'subject_id' => $subject->id,
                        'student_id' => $studentsByNo->get($studentNo)->id,
                        'attendance_date' => $date,
                    ],
                    [
                        'status' => $statusMatch,
                        'recorded_by_user_id' => $userId,
                    ]
                );
            }

            $imported++;
        }

        fclose($handle);

        return response()->json([
            'ok' => true,
            'message' => $dryRun
                ? $imported . ' row(s) checked, ready to import.'
                : $imported . ' attendance record(s) imported.',
            'imported' => $imported,
            'errors' => $errors,
            'summary' => $this->summaryFor($subject),
        ]);
    }

    private function summaryFor(Subject $subject)
    {
        $counts = AttendanceRecord::query()
            ->where('subject_id', $subject->id)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $sessions = AttendanceRecord::query()
            ->where('subject_id', $subject->id)
            ->distinct('attendance_date')
            ->count('attendance_date');

        return [
            'sessions' => (int) $sessions,
            'present' => (int) ($counts['Present'] ?? 0),
            'absent' => (int) ($counts['Absent'] ?? 0),
            'late' => (int) ($counts['Late'] ?? 0),
            'excused' => (int) ($counts['Excused'] ?? 0),
        ];
    }

    private function normalizeDate($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return date('Y-m-d');
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d', $timestamp);
    }

    private function resolveState(Request $request)
    {
        list($schoolYears, $semesterMap) = $this->configuredSchoolSemesterOptions();

        $selectedSchoolYear = trim((string) $request->query('school_year', ''));
        if ($selectedSchoolYear !== '' && !in_array($selectedSchoolYear, $schoolYears, true)) {
            $selectedSchoolYear = '';
        }

        $semesterOptions = $this->semesterOptions($semesterMap, $selectedSchoolYear);

        $selectedSemester = $this->normalizeSemesterValue($request->query('semester', ''));
        if ($selectedSemester !== '' && !$this->optionContainsValue($semesterOptions, $selectedSemester)) {
            $selectedSemester = '';
        }

        $search = trim((string) $request->query('q', ''));

        $hasTermFilter = $selectedSchoolYear !== '' || $selectedSemester !== '';
        $academicTermIds = $this->resolveAcademicTermIds($selectedSchoolYear, $selectedSemester, $hasTermFilter);

        return [
            'search' => $search,
            'selected_school_year' => $selectedSchoolYear,
            'selected_semester' => $selectedSemester,
            'school_year_options' => $this->schoolYearOptions($schoolYears),
            'semester_options' => $semesterOptions,
            'semester_map' => $semesterMap,
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

    private function configuredSchoolSemesterOptions()
    {
        $configOptions = SystemConfigSchoolTermOptions::resolveOptions();

        $schoolYears = array_values($configOptions['school_years'] ?? []);
        $semesterMap = is_array($configOptions['semester_map'] ?? null)
            ? $configOptions['semester_map']
            : [];

        foreach ($semesterMap as $schoolYear => $semesters) {
            $normalizedSemesters = array_values(array_filter(array_map(function ($semester) {
                return $this->normalizeSemesterValue((string) $semester);
            }, (array) $semesters), function ($semester) {
                return $semester !== '';
            }));

            usort($normalizedSemesters, function ($left, $right) {
                return $this->semesterWeight($left) <=> $this->semesterWeight($right);
            });

            $semesterMap[$schoolYear] = array_values(array_unique($normalizedSemesters));
        }

        return [$schoolYears, $semesterMap];
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
