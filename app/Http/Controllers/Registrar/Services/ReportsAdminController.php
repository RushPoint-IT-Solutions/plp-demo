<?php

namespace App\Http\Controllers\Registrar\Services;

use App\AcademicTerm;
use App\CertificateIssued;
use App\Course;
use App\GraduateTagging;
use App\Http\Controllers\Controller;
use App\Support\CorTorReportData;
use App\Support\SystemConfigSchoolTermOptions;
use App\Student;
use App\StudentSubjectGrade;
use App\Subject;
use App\YearBlock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportsAdminController extends Controller
{
    public function studentSearch(Request $request): JsonResponse
    {
        $rawSearch = $request->query('q', '');
        $search = is_string($rawSearch) || is_numeric($rawSearch) ? trim((string) $rawSearch) : '';

        $query = Student::query()
            ->select('students.id', 'students.student_no', 'students.name')
            ->orderBy('students.name')
            ->limit(20);

        $hasProfiles = Schema::hasTable('student_profiles');
        if ($hasProfiles) {
            if (Schema::hasColumn('student_profiles', 'student_id')) {
                $query->leftJoin('student_profiles as sp', 'sp.student_id', '=', 'students.id');
            } else {
                $query->leftJoin('student_profiles as sp', 'sp.student_no', '=', 'students.student_no');
            }

            $query->addSelect('sp.first_name', 'sp.middle_name', 'sp.last_name');
        }

        if ($search !== '') {
            $like = '%' . $search . '%';
            $query->where(function ($builder) use ($like, $hasProfiles) {
                $builder->where('students.student_no', 'like', $like)
                    ->orWhere('students.name', 'like', $like);

                if ($hasProfiles) {
                    $builder->orWhere('sp.first_name', 'like', $like)
                        ->orWhere('sp.middle_name', 'like', $like)
                        ->orWhere('sp.last_name', 'like', $like);
                }
            });
        }

        $students = $query->get()->map(function ($student) {
            $profileName = trim(implode(' ', array_filter([
                trim((string) ($student->first_name ?? '')),
                trim((string) ($student->middle_name ?? '')),
                trim((string) ($student->last_name ?? '')),
            ])));
            $name = trim((string) ($student->name ?: $profileName));
            $studentNo = trim((string) $student->student_no);

            return [
                'id' => (int) $student->id,
                'student_no' => $studentNo,
                'name' => $name,
                'label' => trim($studentNo . ' - ' . $name, ' -'),
            ];
        });

        return response()->json(['results' => $students]);
    }

    public function batchPrint(Request $request)
    {
        $courseOptions = Course::query()->orderBy('code')->get(['id', 'code', 'name']);
        $yearBlockOptions = YearBlock::query()->orderBy('id')->get(['id', 'label']);

        return view('registrar.services.reports-admin.batch-print', compact('courseOptions', 'yearBlockOptions'));
    }

    public function batchPrintSections(Request $request): JsonResponse
    {
        $courseId = (int) $request->query('course_id', 0);
        $yearBlockId = (int) $request->query('year_block_id', 0);

        $sections = collect();
        if (Schema::hasTable('student_section_assignments')) {
            $sections = DB::table('student_section_assignments')
                ->where('status', 'active')
                ->whereRaw("TRIM(COALESCE(section, '')) <> ''")
                ->when($courseId > 0, function ($query) use ($courseId) {
                    $query->where('course_id', $courseId);
                })
                ->when($yearBlockId > 0, function ($query) use ($yearBlockId) {
                    $query->where('year_block_id', $yearBlockId);
                })
                ->distinct()
                ->orderBy('section')
                ->pluck('section');
        }

        return response()->json(['results' => $sections->filter()->values()]);
    }

    public function batchPrintStudents(Request $request): JsonResponse
    {
        $courseId = (int) $request->query('course_id', 0);
        $yearBlockId = (int) $request->query('year_block_id', 0);
        $section = trim((string) $request->query('section', ''));

        if ($section !== '' && Schema::hasTable('student_section_assignments')) {
            $students = Student::query()
                ->select('students.id', 'students.student_no', 'students.name')
                ->join('student_section_assignments as ssa', 'ssa.student_id', '=', 'students.id')
                ->where('ssa.status', 'active')
                ->where('ssa.section', $section)
                ->when($courseId > 0, function ($query) use ($courseId) {
                    $query->where('ssa.course_id', $courseId);
                })
                ->when($yearBlockId > 0, function ($query) use ($yearBlockId) {
                    $query->where('ssa.year_block_id', $yearBlockId);
                })
                ->distinct()
                ->orderBy('students.name')
                ->get();
        } else {
            $students = Student::query()
                ->select('id', 'student_no', 'name')
                ->when($courseId > 0, function ($query) use ($courseId) {
                    $query->where('course_id', $courseId);
                })
                ->when($yearBlockId > 0, function ($query) use ($yearBlockId) {
                    $query->where('year_block_id', $yearBlockId);
                })
                ->orderBy('name')
                ->limit(300)
                ->get();
        }

        return response()->json([
            'results' => $students->map(function (Student $student) {
                return [
                    'id' => (int) $student->id,
                    'student_no' => (string) $student->student_no,
                    'name' => (string) $student->name,
                ];
            })->values(),
        ]);
    }

    public function batchPrintRender(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:cor,tor'],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer'],
        ]);

        $scopedCourseIds = \App\Support\CourseScopeGate::allowedCourseIds($request->user());

        $students = Student::query()
            ->when($scopedCourseIds !== null, function ($query) use ($scopedCourseIds) {
                $query->whereIn('course_id', $scopedCourseIds);
            })
            ->whereIn('id', $validated['student_ids'])
            ->orderBy('name')
            ->get();

        if ($students->isEmpty()) {
            abort(404, 'No matching students found for the selected batch.');
        }

        if ($validated['type'] === 'cor') {
            $entries = $students->map(function (Student $student) {
                return CorTorReportData::corForStudent($student);
            });

            return view('registrar.services.reports-admin.batch-print-cor', compact('entries'));
        }

        $entries = $students->map(function (Student $student) {
            return CorTorReportData::torForStudent($student);
        });

        return view('registrar.services.reports-admin.batch-print-tor', compact('entries'));
    }

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
        $hasStudentProgram = Schema::hasColumn('students', 'program');
        $hasSubjectCourse = Schema::hasColumn('subjects', 'course');

        $hasStudentYearLevel = Schema::hasColumn('students', 'year_level');
        $hasStudentCourseId = Schema::hasColumn('students', 'course_id');
        $hasStudentYearBlockId = Schema::hasColumn('students', 'year_block_id');

        // Base list: every student (subject to search/program filters), so the report lists
        // everyone — not only students who happen to already have a posted grade.
        // program/year_level aren't always real columns here — they can be resolved via the
        // canonicalCourse/yearBlock relations instead (same pattern used elsewhere in this app).
        $studentQuery = Student::query();
        if ($hasStudentCourseId) {
            $studentQuery->with('canonicalCourse');
        }
        if ($hasStudentYearBlockId) {
            $studentQuery->with('yearBlock');
        }
        if ($search !== '') {
            $studentQuery->where(function ($query) use ($search) {
                $query->where('student_no', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%');
            });
        }
        if ($program !== '') {
            if ($hasStudentProgram) {
                $studentQuery->where('program', $program);
            } elseif ($hasStudentCourseId) {
                $studentQuery->whereHas('canonicalCourse', function ($courseQuery) use ($program) {
                    $courseQuery->where('code', $program)->orWhere('name', $program);
                });
            }
        }
        $studentColumns = array_values(array_filter([
            'id', 'student_no', 'name',
            $hasStudentProgram ? 'program' : null,
            $hasStudentYearLevel ? 'year_level' : null,
            $hasStudentCourseId ? 'course_id' : null,
            $hasStudentYearBlockId ? 'year_block_id' : null,
        ]));
        $students = $studentQuery->orderBy('name')->get($studentColumns);

        $gradeQuery = StudentSubjectGrade::query()
            ->with('subject')
            ->whereIn('student_id', $students->pluck('id'))
            ->whereNotNull('final_average');

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

        if ($program !== '' && !$hasStudentProgram && $hasSubjectCourse) {
            $gradeQuery->whereHas('subject', function ($subjectQuery) use ($program) {
                $subjectQuery->where('course', $program);
            });
        }

        $gradesByStudent = $gradeQuery->get()->groupBy('student_id');

        // One row per student — their overall GWA (grade equivalent, 1.00-5.00 scale,
        // PE/NSTP excluded) across every semester in scope, not fragmented per term.
        $rows = $students
            ->map(function ($student) use ($gradesByStudent) {
                $grades = $gradesByStudent->get($student->id, collect());

                $totalUnits = 0.0;
                $weightedTotal = 0.0;
                $subjectsCount = 0;

                foreach ($grades as $grade) {
                    $subject = $grade->subject;
                    $code = strtoupper(trim((string) optional($subject)->code));
                    if ($code === '' || strpos($code, 'PE') === 0 || strpos($code, 'NSTP') === 0) {
                        continue;
                    }

                    $eqGrade = $this->gwaEquivalentGrade((float) $grade->final_average, $subject);
                    if ($eqGrade === null) {
                        continue;
                    }

                    $units = (float) optional($subject)->units;
                    if ($units <= 0) {
                        $units = 1.0;
                    }

                    $totalUnits += $units;
                    $weightedTotal += $eqGrade * $units;
                    $subjectsCount++;
                }

                $gwa = $totalUnits > 0 ? round($weightedTotal / $totalUnits, 2) : null;

                return [
                    'student_id' => (int) $student->id,
                    'student_no' => (string) $student->student_no,
                    'student_name' => (string) $student->name,
                    'program' => (string) ($student->program ?: (optional($student->canonicalCourse)->code ?: optional($student->canonicalCourse)->name ?: '')),
                    'year_level' => (string) ($student->year_level ?: (optional($student->yearBlock)->label ?: '')),
                    'subjects_count' => $subjectsCount,
                    'total_units' => $totalUnits,
                    'gwa' => $gwa,
                ];
            })
            ->sort(function ($left, $right) {
                return strcasecmp((string) $left['student_name'], (string) $right['student_name']);
            })
            ->values();

        $schoolYears = $this->gwaSchoolYearOptions($hasSubjectSchoolYear, $hasSubjectAcademicTerm);
        $semesters = $this->gwaSemesterOptions($hasSubjectSemester, $hasSubjectTerm, $hasSubjectAcademicTerm);

        // The current schema links students to programs via students.course_id
        // -> courses.code, not a legacy program/course text column, so pull the
        // live list from the courses table (falls back to the legacy columns
        // too, in case this runs against an older schema variant).
        $programs = collect()
            ->merge($hasStudentCourseId && Schema::hasTable('courses') ? Course::query()->orderBy('code')->pluck('code') : collect())
            ->merge($hasStudentProgram ? Student::query()->whereNotNull('program')->where('program', '<>', '')->distinct()->orderBy('program')->pluck('program') : collect())
            ->merge($hasSubjectCourse ? Subject::query()->whereNotNull('course')->where('course', '<>', '')->distinct()->orderBy('course')->pluck('course') : collect())
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $summary = [
            'students' => $rows->count(),
            'records' => $rows->sum('subjects_count'),
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

    /**
     * CWA Report — every student's Current Weighted Average: the grade equivalent
     * (1.00-5.00 scale, PE/NSTP excluded) for whichever semester is each student's own
     * most recent one, plus how many of that semester's subjects still need a grade.
     */
    public function cwaReport(Request $request)
    {
        $search = $this->gwaQueryString($request, 'q');
        $program = $this->gwaQueryString($request, 'program');
        $yearLevel = $this->gwaQueryString($request, 'year_level');
        $hasStudentProgram = Schema::hasColumn('students', 'program');
        $hasStudentYearLevel = Schema::hasColumn('students', 'year_level');
        $hasStudentCourseId = Schema::hasColumn('students', 'course_id');
        $hasStudentYearBlockId = Schema::hasColumn('students', 'year_block_id');

        $studentQuery = Student::query()->with(['subjects.academicTerm']);
        if ($hasStudentCourseId) {
            $studentQuery->with('canonicalCourse');
        }
        if ($hasStudentYearBlockId) {
            $studentQuery->with('yearBlock');
        }
        if ($search !== '') {
            $studentQuery->where(function ($query) use ($search) {
                $query->where('student_no', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%');
            });
        }
        if ($program !== '') {
            if ($hasStudentProgram) {
                $studentQuery->where('program', $program);
            } elseif ($hasStudentCourseId) {
                $studentQuery->whereHas('canonicalCourse', function ($courseQuery) use ($program) {
                    $courseQuery->where('code', $program)->orWhere('name', $program);
                });
            }
        }
        if ($yearLevel !== '' && $hasStudentYearLevel) {
            $studentQuery->where('year_level', $yearLevel);
        }

        $students = $studentQuery->orderBy('name')->get();

        $gradesByStudent = StudentSubjectGrade::with('subject')
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->groupBy('student_id');

        $rows = $students
            ->map(function ($student) use ($gradesByStudent) {
                $enrolledSubjects = $student->subjects;
                $currentLabel = '';

                if ($enrolledSubjects->isNotEmpty()) {
                    $currentSubject = $enrolledSubjects->sortByDesc('academic_term_id')->first();
                    $currentLabel = trim((string) $currentSubject->school_year . ' ' . (string) $currentSubject->semester);
                }

                $studentGrades = $gradesByStudent->get($student->id, collect())->keyBy('subject_id');

                // CWA = Cumulative Weighted Average — computed across ALL of the student's
                // enrolled subjects to date, not just their most recent semester.
                $totalUnits = 0.0;
                $weightedTotal = 0.0;
                $postedCount = 0;

                foreach ($enrolledSubjects as $subject) {
                    $grade = $studentGrades->get($subject->id);
                    if (!$grade || $grade->final_average === null) {
                        continue;
                    }

                    $postedCount++;

                    $code = strtoupper(trim((string) $subject->code));
                    if ($code === '' || strpos($code, 'PE') === 0 || strpos($code, 'NSTP') === 0) {
                        continue;
                    }

                    $eqGrade = $this->gwaEquivalentGrade((float) $grade->final_average, $subject);
                    if ($eqGrade === null) {
                        continue;
                    }

                    $units = (float) $subject->units;
                    if ($units <= 0) {
                        $units = 1.0;
                    }

                    $totalUnits += $units;
                    $weightedTotal += $eqGrade * $units;
                }

                $cwa = $totalUnits > 0 ? round($weightedTotal / $totalUnits, 2) : null;
                $missingCount = max(0, $enrolledSubjects->count() - $postedCount);

                return [
                    'student_id' => (int) $student->id,
                    'student_no' => (string) $student->student_no,
                    'student_name' => (string) $student->name,
                    'program' => (string) ($student->program ?: (optional($student->canonicalCourse)->code ?: optional($student->canonicalCourse)->name ?: '')),
                    'year_level' => (string) ($student->year_level ?: (optional($student->yearBlock)->label ?: '')),
                    'current_semester' => $currentLabel,
                    'subjects_count' => $enrolledSubjects->count(),
                    'missing_count' => $missingCount,
                    'cwa' => $cwa,
                ];
            })
            ->sort(function ($left, $right) {
                return strcasecmp((string) $left['student_name'], (string) $right['student_name']);
            })
            ->values();

        $programs = collect()
            ->merge($hasStudentProgram ? Student::query()->whereNotNull('program')->where('program', '<>', '')->distinct()->orderBy('program')->pluck('program') : collect())
            ->merge(Schema::hasColumn('subjects', 'course') ? \App\Subject::query()->whereNotNull('course')->where('course', '<>', '')->distinct()->orderBy('course')->pluck('course') : collect())
            ->filter()
            ->unique()
            ->values();

        $yearLevels = $hasStudentYearLevel
            ? Student::query()->whereNotNull('year_level')->where('year_level', '<>', '')->distinct()->orderBy('year_level')->pluck('year_level')->values()
            : collect();

        $summary = [
            'students' => $rows->count(),
            'missing_grades' => $rows->sum('missing_count'),
            'average_cwa' => $rows->filter(function ($row) {
                return is_array($row) && ($row['cwa'] ?? null) !== null;
            })->avg('cwa'),
        ];

        return view('registrar.services.reports-admin.cwa-report', compact(
            'rows',
            'programs',
            'yearLevels',
            'summary',
            'search',
            'program',
            'yearLevel'
        ));
    }

    /**
     * Grade equivalent (1.00-5.00 point scale) for a raw percentage average, using the
     * subject's own transmutation table (course-specific, falling back to global/defaults).
     */
    private function gwaEquivalentGrade(float $rawAverage, $subject): ?float
    {
        foreach ($this->gwaTransmutationRulesForSubject($subject) as $rule) {
            if ($rawAverage >= $rule['from'] && $rawAverage <= $rule['to']) {
                return $rule['grade'];
            }
        }

        return null;
    }

    private function gwaTransmutationRulesForSubject($subject): array
    {
        if (!$subject || !Schema::hasTable('transmutation_rules')) {
            return $this->gwaDefaultTransmutationBands();
        }

        if (Schema::hasColumn('transmutation_rules', 'course_id') && !empty($subject->course_id)) {
            $rows = DB::table('transmutation_rules')
                ->where('course_id', $subject->course_id)
                ->orderByDesc('initial_from')
                ->get();

            if ($rows->isNotEmpty()) {
                return $this->gwaFormatTransmutationRules($rows);
            }
        }

        $global = DB::table('transmutation_rules');
        if (Schema::hasColumn('transmutation_rules', 'course_id')) {
            $global->whereNull('course_id');
        }
        $rows = $global->orderByDesc('initial_from')->get();

        return $rows->isNotEmpty() ? $this->gwaFormatTransmutationRules($rows) : $this->gwaDefaultTransmutationBands();
    }

    private function gwaFormatTransmutationRules($rows): array
    {
        return $rows->map(function ($rule) {
            return [
                'from' => (float) $rule->initial_from,
                'to' => (float) $rule->initial_to,
                'grade' => (float) $rule->transmuted_grade,
            ];
        })->values()->all();
    }

    private function gwaDefaultTransmutationBands(): array
    {
        return [
            ['from' => 97.50, 'to' => 100.00, 'grade' => 1.00],
            ['from' => 94.50, 'to' => 97.49, 'grade' => 1.25],
            ['from' => 91.50, 'to' => 94.49, 'grade' => 1.50],
            ['from' => 88.50, 'to' => 91.49, 'grade' => 1.75],
            ['from' => 85.50, 'to' => 88.49, 'grade' => 2.00],
            ['from' => 82.50, 'to' => 85.49, 'grade' => 2.25],
            ['from' => 79.50, 'to' => 82.49, 'grade' => 2.50],
            ['from' => 76.50, 'to' => 79.49, 'grade' => 2.75],
            ['from' => 74.50, 'to' => 76.49, 'grade' => 3.00],
            ['from' => 0.00, 'to' => 74.49, 'grade' => 5.00],
        ];
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
            'latin_honor' => 'nullable|string|in:With Honors,Cum Laude,Magna Cum Laude,Summa Cum Laude',
            'so_date' => 'nullable|date',
            'suspend_account' => 'nullable|boolean',
            'suspend_remarks' => 'nullable|string|max:255|required_if:suspend_account,1',
        ], [], [
            'so_number' => 'BOR Number',
            'so_date' => 'BOR Date',
            'latin_honor' => 'Latin Honor',
        ]);

        GraduateTagging::updateOrCreate(
            ['student_id' => $student->id],
            [
                'is_graduate' => (bool) ($validated['is_graduate'] ?? false),
                'date_graduated' => $validated['date_graduated'] ?? null,
                'so_number' => isset($validated['so_number']) ? trim((string) $validated['so_number']) : null,
                'latin_honor' => isset($validated['latin_honor']) && $validated['latin_honor'] !== '' ? $validated['latin_honor'] : null,
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
