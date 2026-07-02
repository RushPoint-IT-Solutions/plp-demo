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

    public function gwaReport(Request $request)
    {
        $search = $this->gwaQueryString($request, 'q');
        $schoolYear = $this->gwaQueryString($request, 'school_year');
        $semester = $this->gwaQueryString($request, 'semester');
        $program = $this->gwaQueryString($request, 'program');
        $hasSubjectSchoolYear = Schema::hasColumn('subjects', 'school_year');
        $hasSubjectSemester = Schema::hasColumn('subjects', 'semester');
        $hasSubjectTerm = Schema::hasColumn('subjects', 'term');
        $hasSubjectAcademicTerm = Schema::hasColumn('subjects', 'academic_term_id') && Schema::hasTable('academic_terms');

        $gradeQuery = StudentSubjectGrade::query()
            ->with(['student', 'subject.academicTerm'])
            ->whereNotNull('final_average')
            ->whereHas('student')
            ->whereHas('subject');

        if (Schema::hasColumn('student_subject_grades', 'final_posted_at')) {
            $gradeQuery->whereNotNull('final_posted_at');
        }

        if ($schoolYear !== '') {
            $gradeQuery->whereHas('subject', function ($subjectQuery) use ($schoolYear, $hasSubjectSchoolYear, $hasSubjectAcademicTerm) {
                if ($hasSubjectSchoolYear) {
                    $subjectQuery->where('school_year', $schoolYear);
                } elseif ($hasSubjectAcademicTerm) {
                    $subjectQuery->whereHas('academicTerm', function ($termQuery) use ($schoolYear) {
                        $termQuery->where('school_year', $schoolYear);
                    });
                }
            });
        }

        if ($semester !== '') {
            $gradeQuery->whereHas('subject', function ($subjectQuery) use ($semester, $hasSubjectSemester, $hasSubjectTerm, $hasSubjectAcademicTerm) {
                if ($hasSubjectSemester) {
                    $subjectQuery->where('semester', $semester);
                } elseif ($hasSubjectTerm) {
                    $subjectQuery->where('term', $semester);
                } elseif ($hasSubjectAcademicTerm) {
                    $subjectQuery->whereHas('academicTerm', function ($termQuery) use ($semester) {
                        $termQuery->where('term', $semester);
                    });
                }
            });
        }

        if ($program !== '') {
            $gradeQuery->where(function ($query) use ($program) {
                $query->whereHas('student', function ($studentQuery) use ($program) {
                    $studentQuery->where('program', $program);
                })->orWhereHas('subject', function ($subjectQuery) use ($program) {
                    $subjectQuery->where('course', $program);
                });
            });
        }

        if ($search !== '') {
            $gradeQuery->whereHas('student', function ($studentQuery) use ($search) {
                $studentQuery->where('student_no', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%');
            });
        }

        $gradeRows = $gradeQuery->get();

        $rows = $gradeRows
            ->groupBy(function ($grade) {
                $subject = $grade->subject;

                return implode('|', [
                    $grade->student_id,
                    $this->gwaSubjectSchoolYear($subject),
                    $this->gwaSubjectSemester($subject),
                ]);
            })
            ->map(function ($grades) {
                $first = $grades->first();
                $student = $first->student;
                $schoolYear = $this->gwaSubjectSchoolYear($first->subject);
                $semester = $this->gwaSubjectSemester($first->subject);

                $totalUnits = 0.0;
                $weightedTotal = 0.0;

                foreach ($grades as $grade) {
                    $units = (float) optional($grade->subject)->units;
                    if ($units <= 0) {
                        $units = 1.0;
                    }

                    $totalUnits += $units;
                    $weightedTotal += ((float) $grade->final_average) * $units;
                }

                $gwa = $totalUnits > 0 ? round($weightedTotal / $totalUnits, 2) : null;

                return [
                    'student_id' => $student ? $student->id : null,
                    'student_no' => $student ? (string) $student->student_no : '',
                    'student_name' => $student ? (string) $student->name : '',
                    'program' => $student ? (string) ($student->program ?: optional($first->subject)->course) : (string) optional($first->subject)->course,
                    'year_level' => $student ? (string) ($student->year_level ?: '') : '',
                    'school_year' => $schoolYear,
                    'semester' => $semester,
                    'subjects_count' => $grades->count(),
                    'total_units' => $totalUnits,
                    'gwa' => $gwa,
                ];
            })
            ->sort(function ($left, $right) {
                $leftSchoolYear = (string) ($left['school_year'] ?? '');
                $rightSchoolYear = (string) ($right['school_year'] ?? '');
                $schoolYearOrder = strcmp($rightSchoolYear, $leftSchoolYear);

                if ($schoolYearOrder !== 0) {
                    return $schoolYearOrder;
                }

                $leftSemesterWeight = $this->gwaSemesterSortWeight($left['semester'] ?? '');
                $rightSemesterWeight = $this->gwaSemesterSortWeight($right['semester'] ?? '');

                if ($leftSemesterWeight !== $rightSemesterWeight) {
                    return $leftSemesterWeight <=> $rightSemesterWeight;
                }

                return strcasecmp(
                    (string) ($left['student_name'] ?? ''),
                    (string) ($right['student_name'] ?? '')
                );
            })
            ->values();

        $schoolYears = $this->gwaSchoolYearOptions($hasSubjectSchoolYear, $hasSubjectAcademicTerm);
        $semesters = $this->gwaSemesterOptions($hasSubjectSemester, $hasSubjectTerm, $hasSubjectAcademicTerm);

        $programs = collect()
            ->merge(Schema::hasColumn('students', 'program') ? Student::query()->whereNotNull('program')->where('program', '<>', '')->distinct()->orderBy('program')->pluck('program') : collect())
            ->merge(Schema::hasColumn('subjects', 'course') ? \App\Subject::query()->whereNotNull('course')->where('course', '<>', '')->distinct()->orderBy('course')->pluck('course') : collect())
            ->filter()
            ->unique()
            ->values();

        $summary = [
            'students' => $rows->pluck('student_id')->filter()->unique()->count(),
            'records' => $rows->count(),
            'average_gwa' => $rows->filter(function ($row) {
                return is_array($row) && ($row['gwa'] ?? null) !== null;
            })->avg('gwa'),
        ];

        return view('registrar.services.reports-admin.gwa-report', compact(
            'rows',
            'schoolYears',
            'semesters',
            'programs',
            'summary',
            'search',
            'schoolYear',
            'semester',
            'program'
        ));
    }

    public function guidanceReports(Request $request)
    {
        $systemConfig = $this->reportSystemConfig($request);

        return view('registrar.services.reports-admin.guidance-reports', compact('systemConfig'));
    }

    private function gwaSemesterSortWeight($semester): int
    {
        $normalized = strtolower(trim((string) $semester));

        if (in_array($normalized, ['first', '1st', '1st semester', 'first semester'], true)) {
            return 1;
        }

        if (in_array($normalized, ['second', '2nd', '2nd semester', 'second semester'], true)) {
            return 2;
        }

        if (in_array($normalized, ['summer', 'summer semester'], true)) {
            return 3;
        }

        return 99;
    }

    private function gwaQueryString(Request $request, string $key): string
    {
        $value = $request->query($key, '');

        return is_string($value) || is_numeric($value) ? trim((string) $value) : '';
    }

    private function gwaSubjectSchoolYear($subject): string
    {
        if (!$subject) {
            return '';
        }

        $schoolYear = trim((string) ($subject->getAttribute('school_year') ?? ''));
        if ($schoolYear !== '') {
            return $schoolYear;
        }

        return trim((string) optional($subject->academicTerm)->school_year);
    }

    private function gwaSubjectSemester($subject): string
    {
        if (!$subject) {
            return '';
        }

        $semester = trim((string) ($subject->getAttribute('semester') ?? ''));
        if ($semester !== '') {
            return $semester;
        }

        $term = trim((string) ($subject->getAttribute('term') ?? ''));
        if ($term !== '') {
            return $term;
        }

        return trim((string) optional($subject->academicTerm)->term);
    }

    private function gwaSchoolYearOptions(bool $hasSubjectSchoolYear, bool $hasSubjectAcademicTerm)
    {
        if ($hasSubjectSchoolYear) {
            return StudentSubjectGrade::query()
                ->join('subjects', 'subjects.id', '=', 'student_subject_grades.subject_id')
                ->whereNotNull('subjects.school_year')
                ->where('subjects.school_year', '<>', '')
                ->distinct()
                ->orderByDesc('subjects.school_year')
                ->pluck('subjects.school_year');
        }

        if ($hasSubjectAcademicTerm) {
            return StudentSubjectGrade::query()
                ->join('subjects', 'subjects.id', '=', 'student_subject_grades.subject_id')
                ->join('academic_terms', 'academic_terms.id', '=', 'subjects.academic_term_id')
                ->whereNotNull('academic_terms.school_year')
                ->where('academic_terms.school_year', '<>', '')
                ->distinct()
                ->orderByDesc('academic_terms.school_year')
                ->pluck('academic_terms.school_year');
        }

        return collect();
    }

    private function gwaSemesterOptions(bool $hasSubjectSemester, bool $hasSubjectTerm, bool $hasSubjectAcademicTerm)
    {
        if ($hasSubjectSemester) {
            return StudentSubjectGrade::query()
                ->join('subjects', 'subjects.id', '=', 'student_subject_grades.subject_id')
                ->whereNotNull('subjects.semester')
                ->where('subjects.semester', '<>', '')
                ->distinct()
                ->orderBy('subjects.semester')
                ->pluck('subjects.semester');
        }

        if ($hasSubjectTerm) {
            return StudentSubjectGrade::query()
                ->join('subjects', 'subjects.id', '=', 'student_subject_grades.subject_id')
                ->whereNotNull('subjects.term')
                ->where('subjects.term', '<>', '')
                ->distinct()
                ->orderBy('subjects.term')
                ->pluck('subjects.term');
        }

        if ($hasSubjectAcademicTerm) {
            return StudentSubjectGrade::query()
                ->join('subjects', 'subjects.id', '=', 'student_subject_grades.subject_id')
                ->join('academic_terms', 'academic_terms.id', '=', 'subjects.academic_term_id')
                ->whereNotNull('academic_terms.term')
                ->where('academic_terms.term', '<>', '')
                ->distinct()
                ->orderBy('academic_terms.term')
                ->pluck('academic_terms.term');
        }

        return collect();
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
