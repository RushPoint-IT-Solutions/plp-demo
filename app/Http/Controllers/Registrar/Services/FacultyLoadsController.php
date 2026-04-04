<?php

namespace App\Http\Controllers\Registrar\Services;

use App\Faculty;
use App\Course;
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
            ->select('school_year')
            ->whereNotNull('school_year')
            ->where('school_year', '!=', '')
            ->distinct()
            ->orderBy('school_year', 'desc')
            ->pluck('school_year');

        $defaultSchoolYear = $schoolYears->first();
        $selectedSchoolYear = (string) $request->query('school_year', $defaultSchoolYear);

        $semesters = Semester::query()->orderBy('id')->get();
        $defaultSemester = optional($semesters->firstWhere('name', '2nd Semester'))->name
            ?? optional($semesters->firstWhere('name', '1st Semester'))->name
            ?? optional($semesters->first())->name;
        $selectedSemester = (string) $request->query('semester', $defaultSemester);

        $tab = (string) $request->query('tab', 'load');
        if (!in_array($tab, ['load', 'loading'], true)) {
            $tab = 'load';
        }

        $assignedSubjects = Subject::query()
            ->where('faculty_id', $faculty->id)
            ->when($selectedSchoolYear !== '', function ($q) use ($selectedSchoolYear) {
                return $q->where('school_year', $selectedSchoolYear);
            })
            ->when($selectedSemester !== '', function ($q) use ($selectedSemester) {
                return $q->where('semester', $selectedSemester);
            })
            ->orderByRaw('COALESCE(course, "")')
            ->orderByRaw('COALESCE(year_section, "")')
            ->orderBy('code')
            ->get();

        $availableSubjects = Subject::query()
            ->whereNull('faculty_id')
            ->when($selectedSchoolYear !== '', function ($q) use ($selectedSchoolYear) {
                return $q->where('school_year', $selectedSchoolYear);
            })
            ->when($selectedSemester !== '', function ($q) use ($selectedSemester) {
                return $q->where('semester', $selectedSemester);
            })
            ->orderBy('code')
            ->get();

        $courseNamesByCode = Course::query()->pluck('name', 'code');

        $groupedSchedule = $assignedSubjects
            ->groupBy(function (Subject $s) use ($courseNamesByCode) {
                $courseCode = (string) ($s->course ?? '');
                if ($courseCode === '') return '—';
                return (string) ($courseNamesByCode[$courseCode] ?? $courseCode);
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
            'lec' => (int) $assignedSubjects->sum(function (Subject $s) { return (int) ($s->lec ?? 0); }),
            'lab' => (int) $assignedSubjects->sum(function (Subject $s) { return (int) ($s->lab ?? 0); }),
            'units' => (float) $assignedSubjects->sum(function (Subject $s) { return (float) ($s->units ?? 0); }),
        ];

        return view('registrar.services.classroom-faculty.faculty-loads.show', compact(
            'faculty',
            'tab',
            'schoolYears',
            'semesters',
            'selectedSchoolYear',
            'selectedSemester',
            'assignedSubjects',
            'availableSubjects',
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
        ]);

        $subject = Subject::query()
            ->where('id', $validated['subject_id'])
            ->whereNull('faculty_id')
            ->firstOrFail();

        $subject->faculty_id = $faculty->id;
        $subject->faculty = $faculty->name; // keep legacy string in sync
        $subject->load_type = $validated['load_type'];
        $subject->credited_tuition_units = $validated['credited_tuition_units'] ?? null;
        $subject->load_hours = $validated['load_hours'] ?? null;
        $subject->added_by = 'Admin1';
        $subject->save();

        return redirect()
            ->route('registrar.services.classroom-faculty.faculty-loads.show', [
                'faculty' => $faculty->id,
                'tab' => 'loading',
                'school_year' => $validated['school_year'] ?? null,
                'semester' => $validated['semester'] ?? null,
            ])
            ->with('status', 'Subject assigned successfully.')
            ->with('status_type', 'success');
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
