<?php

namespace App\Http\Controllers\Registrar\Services;

use App\AcademicTerm;
use App\CertificateIssued;
use App\Course;
use App\GraduateTagging;
use App\Http\Controllers\Controller;
use App\Support\SystemConfigSchoolTermOptions;
use App\Student;
use App\StudentSubjectGrade;
use App\YearBlock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ReportsAdminController extends Controller
{
    public function academicReports(Request $request)
    {
        $totalStudents = Student::query()->count();
        $failingStudents = StudentSubjectGrade::query()
            ->whereNotNull('final_average')
            ->where('final_average', '<', 75.0)
            ->distinct()
            ->count('student_id');
        $students = Student::query()->orderBy('name')->limit(200)->get(['id', 'student_no', 'name']);
        $systemConfig = $this->reportSystemConfig($request);

        $summary = [
            'total_students' => $totalStudents,
            'failing_students' => $failingStudents,
            'passing_students' => max($totalStudents - $failingStudents, 0),
        ];

        return view('registrar.services.reports-admin.academic-reports', compact('summary', 'students', 'systemConfig'));
    }

    public function guidanceReports(Request $request)
    {
        $systemConfig = $this->reportSystemConfig($request);

        return view('registrar.services.reports-admin.guidance-reports', compact('systemConfig'));
    }

    public function certifications(Request $request)
    {
        $students = Student::query()->orderBy('name')->limit(200)->get(['id', 'student_no', 'name']);
        $recentCertificates = CertificateIssued::query()
            ->with('student')
            ->orderByDesc('date_issued')
            ->orderByDesc('id')
            ->limit(20)
            ->get();
        $systemConfig = $this->reportSystemConfig($request);

        return view('registrar.services.reports-admin.certifications', compact('students', 'recentCertificates', 'systemConfig'));
    }

    public function taggingOfGraduates(Request $request)
    {
        $search = trim((string) $request->query('q', $request->query('student', '')));
        $schoolYear = trim((string) $request->query('school_year', ''));
        $semester = trim((string) $request->query('semester', ''));
        $program = trim((string) $request->query('program', ''));
        $yearLevel = trim((string) $request->query('year_level', ''));
        $status = trim((string) $request->query('status', ''));

        $hasStudentSchoolYear = Schema::hasColumn('students', 'school_year');
        $hasStudentSemester = Schema::hasColumn('students', 'semester');
        $hasStudentTerm = Schema::hasColumn('students', 'term');
        $hasStudentProgram = Schema::hasColumn('students', 'program');
        $hasStudentYearLevel = Schema::hasColumn('students', 'year_level');
        $hasAcademicTermId = Schema::hasColumn('students', 'academic_term_id') && Schema::hasTable('academic_terms');
        $hasCourseId = Schema::hasColumn('students', 'course_id') && Schema::hasTable('courses');
        $hasYearBlockId = Schema::hasColumn('students', 'year_block_id') && Schema::hasTable('year_blocks');

        $query = Student::query()
            ->with(['canonicalCourse:id,code,name', 'yearBlock:id,label', 'academicTerm:id,school_year,term', 'graduateTagging'])
            ->orderBy('name');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('student_no', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%');

                if (ctype_digit($search)) {
                    $builder->orWhere('id', (int) $search);
                }
            });
        }

        if ($schoolYear !== '') {
            if ($hasStudentSchoolYear) {
                $query->where('school_year', $schoolYear);
            } elseif ($hasAcademicTermId) {
                $query->whereHas('academicTerm', function ($termQuery) use ($schoolYear) {
                    $termQuery->where('school_year', $schoolYear);
                });
            }
        }

        if ($semester !== '') {
            if ($hasStudentSemester) {
                $query->where('semester', $semester);
            } elseif ($hasStudentTerm) {
                $query->where('term', $semester);
            } elseif ($hasAcademicTermId) {
                $query->whereHas('academicTerm', function ($termQuery) use ($semester) {
                    $termQuery->where('term', $semester);
                });
            }
        }

        if ($program !== '') {
            $query->where(function ($builder) use ($program, $hasStudentProgram, $hasCourseId) {
                if ($hasStudentProgram) {
                    $builder->where('program', $program);
                }

                if ($hasCourseId) {
                    $method = $hasStudentProgram ? 'orWhereHas' : 'whereHas';
                    $builder->{$method}('canonicalCourse', function ($courseQuery) use ($program) {
                        $courseQuery->where('code', $program)
                            ->orWhere('name', $program);
                    });
                }
            });
        }

        if ($yearLevel !== '') {
            if ($hasStudentYearLevel) {
                $query->where('year_level', $yearLevel);
            } elseif ($hasYearBlockId) {
                $query->whereHas('yearBlock', function ($yearQuery) use ($yearLevel) {
                    $yearQuery->where('label', $yearLevel);
                });
            }
        }

        if ($status === 'graduated') {
            $query->whereHas('graduateTagging', function ($tagQuery) {
                $tagQuery->where('is_graduate', true);
            });
        } elseif ($status === 'suspended') {
            $query->whereHas('graduateTagging', function ($tagQuery) {
                $tagQuery->where('suspend_account', true);
            });
        } elseif ($status === 'pending') {
            $query->where(function ($builder) {
                $builder->whereDoesntHave('graduateTagging')
                    ->orWhereHas('graduateTagging', function ($tagQuery) {
                        $tagQuery->where(function ($nested) {
                            $nested->whereNull('is_graduate')->orWhere('is_graduate', false);
                        })->where(function ($nested) {
                            $nested->whereNull('suspend_account')->orWhere('suspend_account', false);
                        });
                    });
            });
        }

        $students = $query->paginate(15)->appends($request->query());

        $studentIds = [];
        foreach ($students->items() as $student) {
            $studentIds[] = $student->id;
        }

        $taggings = GraduateTagging::query()
            ->whereIn('student_id', $studentIds)
            ->get()
            ->keyBy('student_id');

        $schoolYears = $hasStudentSchoolYear
            ? Student::query()->whereNotNull('school_year')->where('school_year', '<>', '')->distinct()->orderByDesc('school_year')->pluck('school_year')
            : ($hasAcademicTermId ? AcademicTerm::query()->whereNotNull('school_year')->where('school_year', '<>', '')->distinct()->orderByDesc('school_year')->pluck('school_year') : collect());

        if ($hasStudentSemester) {
            $semesters = Student::query()->whereNotNull('semester')->where('semester', '<>', '')->distinct()->orderBy('semester')->pluck('semester');
        } elseif ($hasStudentTerm) {
            $semesters = Student::query()->whereNotNull('term')->where('term', '<>', '')->distinct()->orderBy('term')->pluck('term');
        } else {
            $semesters = $hasAcademicTermId ? AcademicTerm::query()->whereNotNull('term')->where('term', '<>', '')->distinct()->orderBy('term')->pluck('term') : collect();
        }

        $programs = $hasStudentProgram
            ? Student::query()->whereNotNull('program')->where('program', '<>', '')->distinct()->orderBy('program')->pluck('program')
            : ($hasCourseId ? Course::query()->orderBy('code')->pluck('code') : collect());

        $yearLevels = $hasStudentYearLevel
            ? Student::query()->whereNotNull('year_level')->where('year_level', '<>', '')->distinct()->orderBy('year_level')->pluck('year_level')
            : ($hasYearBlockId ? YearBlock::query()->orderBy('label')->pluck('label') : collect());

        $summary = [
            'total_students' => Student::query()->count(),
            'graduates' => GraduateTagging::query()->where('is_graduate', true)->count(),
            'suspended' => GraduateTagging::query()->where('suspend_account', true)->count(),
            'matching' => $students->total(),
        ];

        return view('registrar.services.reports-admin.tagging-of-graduates', compact(
            'students',
            'taggings',
            'schoolYears',
            'semesters',
            'programs',
            'yearLevels',
            'summary',
            'search',
            'schoolYear',
            'semester',
            'program',
            'yearLevel',
            'status'
        ));
    }

    public function issueAcademicReport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'report_type' => 'required|string|max:120',
            'purpose' => 'nullable|string|max:255',
            'date_issued' => 'nullable|date',
        ]);

        $student = Student::findOrFail($validated['student_id']);

        $record = CertificateIssued::create([
            'student_id' => $student->id,
            'certificate_type' => trim($validated['report_type']),
            'purpose' => isset($validated['purpose']) ? trim((string) $validated['purpose']) : null,
            'date_issued' => $validated['date_issued'] ?? now()->toDateString(),
            'issued_by' => optional(auth()->user())->name ?: 'Registrar',
            'school_year' => $student->school_year,
            'semester' => $student->semester,
        ]);

        return response()->json(['ok' => true, 'id' => $record->id]);
    }

    public function issueCertification(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'certificate_type' => 'required|string|max:120',
            'purpose' => 'nullable|string|max:255',
            'date_issued' => 'nullable|date',
        ]);

        $student = Student::findOrFail($validated['student_id']);

        $record = CertificateIssued::create([
            'student_id' => $student->id,
            'certificate_type' => trim($validated['certificate_type']),
            'purpose' => isset($validated['purpose']) ? trim((string) $validated['purpose']) : null,
            'date_issued' => $validated['date_issued'] ?? now()->toDateString(),
            'issued_by' => optional(auth()->user())->name ?: 'Registrar',
            'school_year' => $student->school_year,
            'semester' => $student->semester,
        ]);

        return response()->json(['ok' => true, 'id' => $record->id]);
    }

    public function taggingOfGraduatesUpdate(Request $request, Student $student): JsonResponse
    {
        $validated = $request->validate([
            'is_graduate' => 'nullable|boolean',
            'date_graduated' => 'nullable|date',
            'so_number' => 'nullable|string|max:80',
            'so_date' => 'nullable|date',
            'suspend_account' => 'nullable|boolean',
            'suspend_remarks' => 'nullable|string|max:255|required_if:suspend_account,1',
        ]);

        GraduateTagging::updateOrCreate(
            ['student_id' => $student->id],
            [
                'is_graduate' => (bool) ($validated['is_graduate'] ?? false),
                'date_graduated' => $validated['date_graduated'] ?? null,
                'so_number' => isset($validated['so_number']) ? trim((string) $validated['so_number']) : null,
                'so_date' => $validated['so_date'] ?? null,
                'suspend_account' => (bool) ($validated['suspend_account'] ?? false),
                'suspend_remarks' => (bool) ($validated['suspend_account'] ?? false)
                    ? (isset($validated['suspend_remarks']) ? trim((string) $validated['suspend_remarks']) : null)
                    : null,
            ]
        );

        return response()->json(['ok' => true]);
    }

    private function reportSystemConfig(Request $request): array
    {
        $configOptions = SystemConfigSchoolTermOptions::resolveOptions();

        $schoolYearOptions = array_values($configOptions['school_years'] ?? []);
        $semesterMap = is_array($configOptions['semester_map'] ?? null)
            ? $configOptions['semester_map']
            : [];

        $selectedSchoolYear = trim((string) $request->query('school_year', (string) ($configOptions['default_school_year'] ?? '')));
        if ($selectedSchoolYear === '' || !in_array($selectedSchoolYear, $schoolYearOptions, true)) {
            $selectedSchoolYear = count($schoolYearOptions)
                ? (string) $schoolYearOptions[0]
                : '';
        }

        $termOptions = SystemConfigSchoolTermOptions::semesterOptionsForYear($semesterMap, $selectedSchoolYear);
        $selectedTerm = SystemConfigSchoolTermOptions::normalizeSemester((string) $request->query('term', (string) ($configOptions['default_semester'] ?? '')));
        if ($selectedTerm === '' || !in_array($selectedTerm, $termOptions, true)) {
            $selectedTerm = count($termOptions)
                ? (string) $termOptions[0]
                : (string) ($configOptions['default_semester'] ?? 'First');
        }

        return [
            'schoolYearOptions' => $schoolYearOptions,
            'termOptions' => $termOptions,
            'semesterMap' => $semesterMap,
            'selectedSchoolYear' => $selectedSchoolYear,
            'selectedTerm' => $selectedTerm,
        ];
    }
}
