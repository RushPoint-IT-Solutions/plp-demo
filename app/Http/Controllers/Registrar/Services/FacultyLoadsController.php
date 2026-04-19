<?php

namespace App\Http\Controllers\Registrar\Services;

use App\Faculty;
use App\Http\Controllers\Controller;
use App\Semester;
use App\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FacultyLoadsController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $faculties = Faculty::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where('code', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%');
            })
            ->orderBy('name')
            ->paginate(10)
            ->appends($request->query());

        return view('registrar.services.classroom-faculty.faculty-loads.index', compact('faculties', 'search'));
    }

    public function show(Request $request, $facultyId)
    {
        $faculty = Faculty::findOrFail($facultyId);

        $schoolYears = Subject::query()
            ->join('academic_terms as at', 'at.id', '=', 'subjects.academic_term_id')
            ->select('at.school_year')
            ->distinct()
            ->orderBy('at.school_year', 'desc')
            ->pluck('school_year');

        $defaultSchoolYear = $schoolYears->first();
        $selectedSchoolYear = (string) $request->query('school_year', $defaultSchoolYear);
        if ($selectedSchoolYear !== '' && !$schoolYears->contains($selectedSchoolYear)) {
            $selectedSchoolYear = (string) $defaultSchoolYear;
        }

        $rawTerms = Subject::query()
            ->join('academic_terms as at', 'at.id', '=', 'subjects.academic_term_id')
            ->when($selectedSchoolYear !== '', function ($q) use ($selectedSchoolYear) {
                return $q->where('at.school_year', $selectedSchoolYear);
            })
            ->select('at.term')
            ->distinct()
            ->pluck('at.term');

        if (!$rawTerms->count()) {
            $rawTerms = Semester::query()->orderBy('id')->pluck('name');
        }

        $semesterLabels = collect();
        foreach ($rawTerms as $rawTerm) {
            $normalized = $this->normalizeSemesterLabel((string) $rawTerm);
            if ($normalized === '') {
                continue;
            }
            if (!$semesterLabels->contains($normalized)) {
                $semesterLabels->push($normalized);
            }
        }

        $semesterLabels = $semesterLabels->sortBy(function ($label) {
            return $this->semesterWeight($label);
        })->values();

        $defaultSemester = '';
        if ($semesterLabels->contains('Second')) {
            $defaultSemester = 'Second';
        } elseif ($semesterLabels->contains('First')) {
            $defaultSemester = 'First';
        } elseif ($semesterLabels->count()) {
            $defaultSemester = (string) $semesterLabels->first();
        }

        $selectedSemester = $this->normalizeSemesterLabel((string) $request->query('semester', $defaultSemester));
        if ($selectedSemester !== '' && !$semesterLabels->contains($selectedSemester)) {
            $selectedSemester = $defaultSemester;
        }

        $schoolYearOptions = $schoolYears
            ->map(function ($sy) {
                return [
                    'value' => (string) $sy,
                    'label' => (string) $sy,
                ];
            })
            ->values()
            ->all();

        $semesterOptions = $semesterLabels
            ->map(function ($label) {
                return [
                    'value' => (string) $label,
                    'label' => (string) $label,
                ];
            })
            ->values()
            ->all();

        $tab = (string) $request->query('tab', 'load');
        if (!in_array($tab, ['load', 'loading'], true)) {
            $tab = 'load';
        }

        $loadingSearch = trim((string) $request->query('loading_q', ''));

        $assignedSubjectsBaseQuery = Subject::query()
            ->with(['academicTerm', 'canonicalCourse'])
            ->withCount('students')
            ->where('faculty_id', $faculty->id)
            ->when($selectedSchoolYear !== '', function ($q) use ($selectedSchoolYear) {
                return $q->whereHas('academicTerm', function ($termQuery) use ($selectedSchoolYear) {
                    $termQuery->where('school_year', $selectedSchoolYear);
                });
            })
            ->when($selectedSemester !== '', function ($q) use ($selectedSemester) {
                return $q->whereHas('academicTerm', function ($termQuery) use ($selectedSemester) {
                    $termQuery->whereIn('term', $this->semesterAliases($selectedSemester));
                });
            })
            ->orderByRaw('CASE WHEN subjects.course_id IS NULL THEN 1 ELSE 0 END')
            ->orderBy('subjects.course_id')
            ->orderByRaw('COALESCE(subjects.year_section, "")')
            ->orderBy('subjects.code');

        $assignedSubjectsForSchedule = (clone $assignedSubjectsBaseQuery)->get();

        $assignedSubjects = (clone $assignedSubjectsBaseQuery)
            ->paginate(10, ['subjects.*'])
            ->appends($request->except('page'));

        $availableSubjectsBaseQuery = Subject::query()
            ->with('canonicalCourse')
            ->whereNull('faculty_id')
            ->when($selectedSchoolYear !== '', function ($q) use ($selectedSchoolYear) {
                return $q->whereHas('academicTerm', function ($termQuery) use ($selectedSchoolYear) {
                    $termQuery->where('school_year', $selectedSchoolYear);
                });
            })
            ->when($selectedSemester !== '', function ($q) use ($selectedSemester) {
                return $q->whereHas('academicTerm', function ($termQuery) use ($selectedSemester) {
                    $termQuery->whereIn('term', $this->semesterAliases($selectedSemester));
                });
            })
            ->when($loadingSearch !== '', function ($q) use ($loadingSearch) {
                $like = '%' . $loadingSearch . '%';

                return $q->where(function ($inner) use ($like) {
                    $inner->where('subjects.code', 'like', $like)
                        ->orWhere('subjects.name', 'like', $like)
                        ->orWhere('subjects.year_section', 'like', $like)
                        ->orWhere('subjects.days', 'like', $like)
                        ->orWhereHas('canonicalCourse', function ($courseQuery) use ($like) {
                            $courseQuery->where('code', 'like', $like)
                                ->orWhere('name', 'like', $like);
                        });
                });
            })
            ->orderByRaw('CASE WHEN subjects.course_id IS NULL THEN 1 ELSE 0 END')
            ->orderBy('subjects.course_id')
            ->orderByRaw('COALESCE(subjects.year_section, "")')
            ->orderBy('subjects.code');

        $availableSubjectsTotal = (clone $availableSubjectsBaseQuery)->count();

        $availableSubjects = (clone $availableSubjectsBaseQuery)
            ->limit(201)
            ->get();

        $availableSubjectsHasMore = $availableSubjectsTotal > 200;
        if ($availableSubjectsHasMore) {
            $availableSubjects = $availableSubjects->take(200)->values();
        }

        $availableSubjectOptions = $availableSubjects
            ->map(function (Subject $sub) {
                $courseCode = trim((string) optional($sub->canonicalCourse)->code);
                $section = trim($courseCode . ' ' . trim((string) $sub->year_section));
                $sectionSuffix = $section !== '' ? (' (' . $section . ')') : '';

                return [
                    'value' => (string) $sub->id,
                    'label' => (string) $sub->code . ' — ' . (string) $sub->name . $sectionSuffix,
                ];
            })
            ->values()
            ->all();

        $groupedSchedule = $assignedSubjectsForSchedule
            ->groupBy(function (Subject $s) {
                $courseName = trim((string) optional($s->canonicalCourse)->name);
                if ($courseName !== '') {
                    return $courseName;
                }

                $courseCode = trim((string) optional($s->canonicalCourse)->code);
                if ($courseCode !== '') {
                    return $courseCode;
                }

                return '—';
            })
            ->map(function ($subjectsByCourse) {
                return $subjectsByCourse
                    ->groupBy(function (Subject $s) {
                        return $this->yearLabelFromSection($s->year_section);
                    })
                    ->map(function ($subjectsByYear) {
                        return $subjectsByYear->groupBy(function (Subject $s) {
                            return ($s->code ?? '') . '|' . ($s->name ?? '');
                        });
                    });
            });

        $totals = [
            'lec' => (int) $assignedSubjectsForSchedule->sum(function (Subject $s) { return (int) ($s->lec ?? 0); }),
            'lab' => (int) $assignedSubjectsForSchedule->sum(function (Subject $s) { return (int) ($s->lab ?? 0); }),
            'units' => (float) $assignedSubjectsForSchedule->sum(function (Subject $s) { return (float) ($s->units ?? 0); }),
        ];

        return view('registrar.services.classroom-faculty.faculty-loads.show', compact(
            'faculty',
            'tab',
            'schoolYears',
            'schoolYearOptions',
            'semesterOptions',
            'selectedSchoolYear',
            'selectedSemester',
            'loadingSearch',
            'assignedSubjects',
            'assignedSubjectsForSchedule',
            'availableSubjectOptions',
            'availableSubjectsHasMore',
            'availableSubjectsTotal',
            'groupedSchedule',
            'totals'
        ));
    }

    public function assign(Request $request, $facultyId)
    {
        $faculty = Faculty::findOrFail($facultyId);

        $validated = $request->validate([
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'load_type' => ['required', 'string', 'in:Regular,Part-time,Temporary Substitution'],
            'credited_tuition_units' => ['nullable', 'numeric', 'between:0,999.99', 'regex:/^\d{1,3}(\.\d{1,2})?$/'],
            'load_hours' => ['nullable', 'numeric', 'between:0,999.99', 'regex:/^\d{1,3}(\.\d{1,2})?$/'],
            'school_year' => ['nullable', 'string'],
            'semester' => ['nullable', 'string'],
            'loading_q' => ['nullable', 'string', 'max:120'],
        ], [
            'subject_id.required' => 'Please select a subject from the available list before adding.',
            'subject_id.exists' => 'The selected subject is no longer available for assignment.',
            'credited_tuition_units.between' => 'Credited Tuition Units must be between 0.00 and 999.99.',
            'credited_tuition_units.regex' => 'Credited Tuition Units must have at most 2 decimal places.',
            'load_hours.between' => 'Load Hours must be between 0.00 and 999.99.',
            'load_hours.regex' => 'Load Hours must have at most 2 decimal places.',
        ]);

        $selectedSchoolYear = trim((string) ($validated['school_year'] ?? ''));
        $selectedSemester = $this->normalizeSemesterLabel((string) ($validated['semester'] ?? ''));

        $subjectQuery = Subject::query()
            ->where('id', $validated['subject_id'])
            ->whereNull('faculty_id')
            ->when($selectedSchoolYear !== '', function ($q) use ($selectedSchoolYear) {
                return $q->whereHas('academicTerm', function ($termQuery) use ($selectedSchoolYear) {
                    $termQuery->where('school_year', $selectedSchoolYear);
                });
            })
            ->when($selectedSemester !== '', function ($q) use ($selectedSemester) {
                return $q->whereHas('academicTerm', function ($termQuery) use ($selectedSemester) {
                    $termQuery->whereIn('term', $this->semesterAliases($selectedSemester));
                });
            });

        $subject = $subjectQuery->first();
        if (!$subject) {
            return redirect()
                ->route('registrar.services.classroom-faculty.faculty-loads.show', [
                    'faculty' => $faculty->id,
                    'tab' => 'loading',
                    'school_year' => $selectedSchoolYear !== '' ? $selectedSchoolYear : null,
                    'semester' => $selectedSemester !== '' ? $selectedSemester : null,
                    'loading_q' => trim((string) ($validated['loading_q'] ?? '')) ?: null,
                ])
                ->withInput()
                ->withErrors([
                    'subject_id' => 'Selected subject is not available for the selected School Year and Term. It may already be assigned.',
                ])
                ->with('status', 'Unable to assign subject. Please choose an available subject and try again.')
                ->with('status_type', 'danger');
        }

        $creditedTuitionUnits = array_key_exists('credited_tuition_units', $validated) && $validated['credited_tuition_units'] !== null
            ? round((float) $validated['credited_tuition_units'], 2)
            : null;

        $loadHours = array_key_exists('load_hours', $validated) && $validated['load_hours'] !== null
            ? round((float) $validated['load_hours'], 2)
            : null;

        $subject->faculty_id = $faculty->id;
        $subject->load_type = $validated['load_type'];
        $subject->credited_tuition_units = $creditedTuitionUnits;
        $subject->load_hours = $loadHours;
        $subject->added_by = trim((string) (optional(auth()->user())->name ?: optional(auth()->user())->username ?: 'Registrar'));
        $subject->save();

        return redirect()
            ->route('registrar.services.classroom-faculty.faculty-loads.show', [
                'faculty' => $faculty->id,
                'tab' => 'loading',
                'school_year' => $selectedSchoolYear !== '' ? $selectedSchoolYear : null,
                'semester' => $selectedSemester !== '' ? $selectedSemester : null,
                'loading_q' => trim((string) ($validated['loading_q'] ?? '')) ?: null,
            ])
            ->with('status', 'Subject assigned successfully.')
            ->with('status_type', 'success');
    }

    public function printStrengthOfClasses(Request $request, $facultyId)
    {
        $faculty = Faculty::findOrFail($facultyId);

        $selectedSchoolYear = trim((string) $request->query('school_year', ''));
        $selectedSemester = $this->normalizeSemesterLabel((string) $request->query('semester', ''));

        $subjects = Subject::query()
            ->with(['canonicalCourse', 'academicTerm'])
            ->withCount('students')
            ->where('faculty_id', $faculty->id)
            ->when($selectedSchoolYear !== '', function ($q) use ($selectedSchoolYear) {
                return $q->whereHas('academicTerm', function ($termQuery) use ($selectedSchoolYear) {
                    $termQuery->where('school_year', $selectedSchoolYear);
                });
            })
            ->when($selectedSemester !== '', function ($q) use ($selectedSemester) {
                return $q->whereHas('academicTerm', function ($termQuery) use ($selectedSemester) {
                    $termQuery->whereIn('term', $this->semesterAliases($selectedSemester));
                });
            })
            ->orderByRaw('CASE WHEN subjects.course_id IS NULL THEN 1 ELSE 0 END')
            ->orderBy('subjects.course_id')
            ->orderByRaw('COALESCE(subjects.year_section, "")')
            ->orderBy('subjects.code')
            ->get();

        $rows = $subjects->map(function (Subject $subject) {
            $subjectHours = is_null($subject->load_hours)
                ? ((float) ($subject->lec ?? 0) + (float) ($subject->lab ?? 0))
                : (float) $subject->load_hours;

            return [
                'subject' => (string) $subject->name,
                'code' => (string) $subject->code,
                'section' => strtoupper(trim((string) $subject->year_section)),
                'days' => strtoupper(str_replace([',', ' '], ['/', ''], (string) $subject->days)),
                'time' => strtoupper((string) $subject->formatted_time),
                'room' => strtoupper(trim((string) $subject->room)),
                'units' => (float) ($subject->units ?? 0),
                'lec' => (int) ($subject->lec ?? 0),
                'lab' => (int) ($subject->lab ?? 0),
                'total_hours' => $subjectHours,
                'students' => (int) ($subject->students_count ?? 0),
                'campus' => 'Pasig',
            ];
        })->values();

        $totals = [
            'lec' => (int) $rows->sum('lec'),
            'lab' => (int) $rows->sum('lab'),
            'units' => (float) $rows->sum('units'),
            'total_hours' => (float) $rows->sum('total_hours'),
            'students' => (int) $rows->sum('students'),
        ];

        $classification = $subjects->pluck('load_type')
            ->filter(function ($value) {
                return trim((string) $value) !== '';
            })
            ->unique()
            ->values()
            ->implode(', ');

        $termLabel = trim(
            ($selectedSemester !== '' ? ($selectedSemester . ' Semester') : '') .
            ($selectedSchoolYear !== '' ? (', SY ' . $selectedSchoolYear) : '')
        );

        if ($termLabel === '') {
            $termLabel = 'SY -';
        }

        $blankRows = max(0, 20 - $rows->count());

        return view('registrar.services.classroom-faculty.faculty-loads.print-strength-of-classes', [
            'faculty' => $faculty,
            'rows' => $rows,
            'totals' => $totals,
            'blankRows' => $blankRows,
            'classification' => $classification !== '' ? $classification : 'N/A',
            'termLabel' => $termLabel,
            'selectedSchoolYear' => $selectedSchoolYear,
            'selectedSemester' => $selectedSemester,
            'generatedAt' => Carbon::now(),
        ]);
    }

    private function normalizeSemesterLabel(string $value): string
    {
        $normalized = strtolower(trim($value));
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

    private function semesterAliases(string $canonicalLabel): array
    {
        if ($canonicalLabel === 'First') {
            return ['First', '1st Semester', 'First Semester'];
        }

        if ($canonicalLabel === 'Second') {
            return ['Second', '2nd Semester', 'Second Semester'];
        }

        if ($canonicalLabel === 'Summer') {
            return ['Summer', 'Summer Semester'];
        }

        return [$canonicalLabel];
    }

    private function semesterWeight(string $canonicalLabel): int
    {
        if ($canonicalLabel === 'First') {
            return 1;
        }

        if ($canonicalLabel === 'Second') {
            return 2;
        }

        if ($canonicalLabel === 'Summer') {
            return 3;
        }

        return 4;
    }

    private function yearLabelFromSection(?string $yearSection): string
    {
        $yearSection = trim((string) $yearSection);
        if ($yearSection === '') return 'Other';

        if (preg_match('/\b([1-5])\b/', $yearSection, $m)) {
            $year = (int) $m[1];
            if ($year === 1) return 'First Year';
            if ($year === 2) return 'Second Year';
            if ($year === 3) return 'Third Year';
            if ($year === 4) return 'Fourth Year';
            if ($year === 5) return 'Fifth Year';
        }

        return 'Other';
    }
}
