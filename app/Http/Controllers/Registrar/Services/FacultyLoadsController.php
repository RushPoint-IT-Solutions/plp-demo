<?php

namespace App\Http\Controllers\Registrar\Services;

use App\Faculty;
use App\Http\Controllers\Controller;
use App\Semester;
use App\Subject;
use Illuminate\Http\Request;

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
            ->paginate(25, ['subjects.*'], 'assigned_page')
            ->appends($request->except('assigned_page'));

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

        $availableSubjects = (clone $availableSubjectsBaseQuery)
            ->limit(201)
            ->get();

        $availableSubjectsHasMore = $availableSubjects->count() > 200;
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
            'credited_tuition_units' => ['nullable', 'numeric', 'min:0'],
            'load_hours' => ['nullable', 'numeric', 'min:0'],
            'school_year' => ['nullable', 'string'],
            'semester' => ['nullable', 'string'],
            'loading_q' => ['nullable', 'string', 'max:120'],
        ]);

        $subject = Subject::query()
            ->where('id', $validated['subject_id'])
            ->whereNull('faculty_id')
            ->firstOrFail();

        $subject->faculty_id = $faculty->id;
        $subject->load_type = $validated['load_type'];
        $subject->credited_tuition_units = $validated['credited_tuition_units'] ?? null;
        $subject->load_hours = $validated['load_hours'] ?? null;
        $subject->added_by = trim((string) (optional(auth()->user())->name ?: optional(auth()->user())->username ?: 'Registrar'));
        $subject->save();

        return redirect()
            ->route('registrar.services.classroom-faculty.faculty-loads.show', [
                'faculty' => $faculty->id,
                'tab' => 'loading',
                'school_year' => $validated['school_year'] ?? null,
                'semester' => $this->normalizeSemesterLabel((string) ($validated['semester'] ?? '')) ?: null,
                'loading_q' => trim((string) ($validated['loading_q'] ?? '')) ?: null,
            ])
            ->with('status', 'Subject assigned successfully.')
            ->with('status_type', 'success');
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
