<?php

namespace App\Http\Controllers\Registrar\Services;

use App\College;
use App\Department;
use App\Faculty;
use App\Http\Controllers\Controller;
use App\MasterFacultyFile;
use App\Semester;
use App\StudentSubjectGrade;
use App\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FacultyLoadsController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $selectedDepartmentId = (int) $request->query('department_id', 0);
        $selectedCollegeId = (int) $request->query('college_id', 0);

        $facultyQuery = Faculty::query()
            ->with(['departmentLookup.college', 'college'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('code', 'like', '%' . $search . '%')
                        ->orWhere('name', 'like', '%' . $search . '%');
                });
            })
            ->when($selectedDepartmentId > 0 && Schema::hasColumn('faculties', 'department_id'), function ($q) use ($selectedDepartmentId) {
                $q->where('department_id', $selectedDepartmentId);
            })
            ->when(
                $selectedCollegeId > 0
                    && (Schema::hasColumn('faculties', 'college_id')
                        || (Schema::hasColumn('faculties', 'department_id') && Schema::hasColumn('departments', 'college_id'))),
                function ($q) use ($selectedCollegeId) {
                $q->where(function ($query) use ($selectedCollegeId) {
                    if (Schema::hasColumn('faculties', 'college_id')) {
                        $query->where('college_id', $selectedCollegeId);
                    }

                    if (Schema::hasColumn('faculties', 'department_id') && Schema::hasColumn('departments', 'college_id')) {
                        $query->orWhereHas('departmentLookup', function ($departmentQuery) use ($selectedCollegeId) {
                            $departmentQuery->where('college_id', $selectedCollegeId);
                        });
                    }
                });
            })
            ->orderBy('name');

        $faculties = (clone $facultyQuery)
            ->paginate(10)
            ->appends($request->query());

        $dashboardFaculties = (clone $facultyQuery)->get();
        $facultyIds = $dashboardFaculties->pluck('id')->map(function ($id) {
            return (int) $id;
        })->all();

        [$currentSchoolYear, $currentSemester] = $this->defaultFacultyLoadTermFilters();

        $loadSummaries = collect();
        if (count($facultyIds) > 0) {
            $loadSummaries = Subject::query()
                ->select('faculty_id', DB::raw('SUM(' . $this->facultyLoadSqlExpression() . ') as current_load'), DB::raw('COUNT(*) as subject_count'))
                ->whereIn('faculty_id', $facultyIds)
                ->when($currentSchoolYear !== '', function ($query) use ($currentSchoolYear) {
                    return $query->whereHas('academicTerm', function ($termQuery) use ($currentSchoolYear) {
                        $termQuery->where('school_year', $currentSchoolYear);
                    });
                })
                ->when($currentSemester !== '', function ($query) use ($currentSemester) {
                    return $query->whereHas('academicTerm', function ($termQuery) use ($currentSemester) {
                        $termQuery->whereIn('term', $this->semesterAliases($currentSemester));
                    });
                })
                ->groupBy('faculty_id')
                ->get()
                ->keyBy('faculty_id')
                ->map(function ($row) {
                    return [
                        'current_load' => (float) ($row->current_load ?? 0),
                        'subject_count' => (int) ($row->subject_count ?? 0),
                    ];
                });
        }

        $loadDashboard = [
            'total_faculty' => (int) $dashboardFaculties->count(),
            'underload_count' => 0,
            'full_load_count' => 0,
            'overload_count' => 0,
            'remaining_load' => 0.0,
            'overload_units' => 0.0,
        ];

        foreach ($dashboardFaculties as $facultyRow) {
            $summary = $loadSummaries->get((int) $facultyRow->id, ['current_load' => 0, 'subject_count' => 0]);
            $computedLoad = $this->computedFacultyLoadSummary(
                (int) $facultyRow->id,
                (float) ($summary['current_load'] ?? 0),
                (int) ($summary['subject_count'] ?? 0)
            );
            $remainingLoad = (float) $computedLoad['remaining_load'];
            $status = (string) $computedLoad['status'];

            if ($status === 'Overload') {
                $loadDashboard['overload_count']++;
                $loadDashboard['overload_units'] += abs($remainingLoad);
            } elseif ($status === 'Full Load') {
                $loadDashboard['full_load_count']++;
            } else {
                $loadDashboard['underload_count']++;
                $loadDashboard['remaining_load'] += max(0, $remainingLoad);
            }

            $loadSummaries->put((int) $facultyRow->id, $computedLoad);
        }

        $loadTermLabel = trim(($currentSemester !== '' ? ($currentSemester . ' Semester') : '') . ($currentSchoolYear !== '' ? (' SY ' . $currentSchoolYear) : ''));

        $colleges = College::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'abbr']);
        $departmentColumns = ['id', 'code', 'description'];
        if (Schema::hasColumn('departments', 'college_id')) {
            $departmentColumns[] = 'college_id';
        }

        $departmentOptions = Department::query()
            ->with('college')
            ->when($selectedCollegeId > 0 && Schema::hasColumn('departments', 'college_id'), function ($query) use ($selectedCollegeId) {
                $query->where('college_id', $selectedCollegeId);
            })
            ->orderBy('description')
            ->get($departmentColumns);

        return view('registrar.services.classroom-faculty.faculty-loads.index', compact(
            'faculties',
            'search',
            'loadSummaries',
            'loadTermLabel',
            'loadDashboard',
            'colleges',
            'departmentOptions',
            'selectedDepartmentId',
            'selectedCollegeId'
        ));
    }

    public function show(Request $request, $facultyId)
    {
        $faculty = Faculty::with(['departmentLookup.college', 'college'])->findOrFail($facultyId);

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

        $tab = (string) $request->query('tab', 'profile');
        if (!in_array($tab, ['profile', 'allowed', 'schedule', 'load', 'loading', 'history', 'grades'], true)) {
            $tab = 'profile';
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

        if ($availableSubjectsTotal === 0 && ($selectedSchoolYear !== '' || $selectedSemester !== '')) {
            $availableSubjectsBaseQuery = Subject::query()
                ->with('canonicalCourse')
                ->whereNull('faculty_id')
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
        }

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

        $currentLoad = (float) $assignedSubjectsForSchedule->sum(function (Subject $s) {
            return $this->facultyLoadValue($s);
        });
        $computedLoad = $this->computedFacultyLoadSummary((int) $faculty->id, $currentLoad, (int) $assignedSubjectsForSchedule->count());
        $maxLoad = (float) $computedLoad['max_load'];
        $remainingLoad = (float) $computedLoad['remaining_load'];

        $profileRow = $this->facultyProfileRow($faculty);
        $profileState = $profileRow && is_array($profileRow->config_payload)
            ? (array) ($profileRow->config_payload['form_state'] ?? [])
            : [];
        $photoPath = trim((string) ($profileState['profile_photo_path'] ?? ''));
        $qualifiedSubjects = $this->qualifiedSubjectsForFaculty((int) $faculty->id);
        $qualifiedSubjectIds = $qualifiedSubjects->pluck('id')->map(function ($id) {
            return (int) $id;
        })->all();
        $availableQualifiedSubjectOptions = Subject::query()
            ->when(count($qualifiedSubjectIds) > 0, function ($query) use ($qualifiedSubjectIds) {
                $query->whereNotIn('id', $qualifiedSubjectIds);
            })
            ->orderBy('code')
            ->orderBy('name')
            ->limit(300)
            ->get(['id', 'code', 'name'])
            ->map(function (Subject $subject) {
                return [
                    'id' => (int) $subject->id,
                    'label' => trim((string) $subject->code . ' - ' . (string) $subject->name),
                ];
            })
            ->values();
        $loadStatus = (string) $computedLoad['status'];

        $departmentName = (string) (optional($faculty->departmentLookup)->description ?: (Schema::hasColumn('faculties', 'department') ? ($faculty->department ?? '') : ''));
        $collegeName = (string) (optional($faculty->college)->name ?: optional(optional($faculty->departmentLookup)->college)->name ?: '');
        $collegeCode = (string) (optional($faculty->college)->abbr ?: optional(optional($faculty->departmentLookup)->college)->abbr ?: optional($faculty->college)->code ?: optional(optional($faculty->departmentLookup)->college)->code ?: '');

        $facultyProfile = [
            'department' => (string) ($profileState['department'] ?? $departmentName),
            'college' => $collegeName,
            'college_code' => $collegeCode,
            'employment_type' => (string) (Schema::hasColumn('faculties', 'employment_type') ? ($faculty->employment_type ?? 'Full-time Teacher') : ($profileState['position'] ?? 'Full-time Teacher')),
            'position' => (string) ($profileState['position'] ?? ''),
            'email' => (string) ($profileState['email_address'] ?? ''),
            'mobile' => (string) ($profileState['mobile_no'] ?? ''),
            'status' => (string) ($profileState['status'] ?? 'Active'),
            'photo_url' => $photoPath !== '' ? asset('storage/' . $photoPath) : '',
            'max_load' => $maxLoad,
            'current_load' => $currentLoad,
            'remaining_load' => $remainingLoad,
            'load_status' => $loadStatus,
            'assigned_subjects' => (int) $assignedSubjectsForSchedule->count(),
            'qualified_subjects' => $qualifiedSubjects,
            'qualified_subject_count' => (int) $qualifiedSubjects->count(),
            'sections' => (int) $assignedSubjectsForSchedule->pluck('year_section')->filter()->unique()->count(),
            'students' => (int) $assignedSubjectsForSchedule->sum(function (Subject $s) {
                return (int) ($s->students_count ?? 0);
            }),
        ];

        $loadHistory = Subject::query()
            ->with(['academicTerm', 'canonicalCourse'])
            ->withCount('students')
            ->where('faculty_id', $faculty->id)
            ->orderByDesc('academic_term_id')
            ->orderBy('code')
            ->limit(150)
            ->get()
            ->groupBy(function (Subject $subject) {
                $term = $subject->academicTerm;
                $schoolYear = trim((string) optional($term)->school_year);
                $semester = $this->normalizeSemesterLabel((string) optional($term)->term);
                return trim(($schoolYear !== '' ? $schoolYear : 'No School Year') . ' ' . ($semester !== '' ? $semester : 'No Term'));
            });

        $gradeHistory = collect();
        if (Schema::hasTable('student_subject_grades')) {
            $gradeHistory = StudentSubjectGrade::query()
                ->join('subjects as s', 's.id', '=', 'student_subject_grades.subject_id')
                ->leftJoin('students as st', 'st.id', '=', 'student_subject_grades.student_id')
                ->leftJoin('academic_terms as at', 'at.id', '=', 's.academic_term_id')
                ->leftJoin('courses as c', 'c.id', '=', 's.course_id')
                ->where('s.faculty_id', $faculty->id)
                ->select([
                    'student_subject_grades.*',
                    's.code as subject_code',
                    's.name as subject_name',
                    's.year_section',
                    'at.school_year',
                    'at.term',
                    'c.code as course_code',
                    'st.student_no',
                    'st.name as student_name',
                ])
                ->orderByDesc('student_subject_grades.updated_at')
                ->limit(200)
                ->get();
        }

        $facultyDepartmentColumns = ['id', 'code', 'description'];
        if (Schema::hasColumn('departments', 'college_id')) {
            $facultyDepartmentColumns[] = 'college_id';
        }
        $facultyDepartmentOptions = Department::query()
            ->with('college')
            ->orderBy('description')
            ->get($facultyDepartmentColumns);
        $facultyCollegeOptions = College::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'abbr']);
        $employmentTypeOptions = Schema::hasTable('teacher_load_settings')
            ? DB::table('teacher_load_settings')->orderBy('employment_type')->pluck('employment_type')->filter()->values()->all()
            : [];
        $employmentTypeOptions = array_values(array_unique(array_merge($employmentTypeOptions, [
            'Full-time Teacher',
            'Part-time Teacher',
            'Department Head',
            'Visiting Lecturer',
        ])));

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
            'totals',
            'facultyProfile',
            'loadHistory',
            'gradeHistory',
            'availableQualifiedSubjectOptions',
            'facultyDepartmentOptions',
            'facultyCollegeOptions',
            'employmentTypeOptions'
        ));
    }

    public function updateProfile(Request $request, $facultyId)
    {
        $faculty = Faculty::findOrFail($facultyId);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:faculties,code,' . $faculty->id],
            'name' => ['required', 'string', 'max:255'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'college_id' => ['nullable', 'integer', 'exists:colleges,id'],
            'employment_type' => ['nullable', 'string', 'max:60'],
            'max_load_units' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
        ]);

        $department = !empty($validated['department_id'])
            ? Department::find((int) $validated['department_id'])
            : null;
        $collegeId = $validated['college_id'] ?? (Schema::hasColumn('departments', 'college_id') ? optional($department)->college_id : null);

        $payload = [
            'code' => $validated['code'],
            'name' => $validated['name'],
        ];

        if (Schema::hasColumn('faculties', 'department')) {
            $payload['department'] = optional($department)->description;
        }
        if (Schema::hasColumn('faculties', 'department_id')) {
            $payload['department_id'] = $validated['department_id'] ?? null;
        }
        if (Schema::hasColumn('faculties', 'college_id')) {
            $payload['college_id'] = $collegeId;
        }
        if (Schema::hasColumn('faculties', 'employment_type')) {
            $payload['employment_type'] = $validated['employment_type'] ?: 'Full-time Teacher';
        }
        if (Schema::hasColumn('faculties', 'max_load_units')) {
            $payload['max_load_units'] = $validated['max_load_units'] ?? null;
        }

        $faculty->update($payload);

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'faculty_id')) {
            $userPayload = ['name' => $validated['name']];

            DB::table('users')
                ->where('faculty_id', $faculty->id)
                ->update($userPayload);
        }

        return back()->with('status', 'Faculty profile updated successfully.');
    }

    public function departments(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $selectedCollegeId = (int) $request->query('college_id', 0);

        $departments = Department::query()
            ->with('college')
            ->withCount('faculties')
            ->when($selectedCollegeId > 0 && Schema::hasColumn('departments', 'college_id'), function ($query) use ($selectedCollegeId) {
                $query->where('college_id', $selectedCollegeId);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('code', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('description')
            ->paginate(12)
            ->appends($request->query());

        $colleges = College::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'abbr']);

        $summary = [
            'departments' => Department::count(),
            'colleges' => College::where('is_active', true)->count(),
            'assigned_faculty' => Schema::hasColumn('faculties', 'department_id')
                ? Faculty::whereNotNull('department_id')->count()
                : 0,
            'unassigned_faculty' => Schema::hasColumn('faculties', 'department_id')
                ? Faculty::whereNull('department_id')->count()
                : Faculty::count(),
        ];

        return view('registrar.registrar-menu.faculty-management.departments', compact('departments', 'colleges', 'search', 'summary', 'selectedCollegeId'));
    }

    public function storeDepartment(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:departments,code'],
            'description' => ['required', 'string', 'max:255', 'unique:departments,description'],
            'college_id' => ['nullable', 'integer', 'exists:colleges,id'],
        ]);

        if (!Schema::hasColumn('departments', 'college_id')) {
            unset($validated['college_id']);
        }

        Department::create($validated);

        return back()->with('success', 'Department created successfully.');
    }

    public function updateDepartment(Request $request, Department $department)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:departments,code,' . $department->id],
            'description' => ['required', 'string', 'max:255', 'unique:departments,description,' . $department->id],
            'college_id' => ['nullable', 'integer', 'exists:colleges,id'],
        ]);

        if (!Schema::hasColumn('departments', 'college_id')) {
            unset($validated['college_id']);
        }

        $department->update($validated);

        if (Schema::hasColumn('departments', 'college_id') && Schema::hasColumn('faculties', 'department_id') && Schema::hasColumn('faculties', 'college_id')) {
            Faculty::where('department_id', $department->id)
                ->where(function ($query) {
                    $query->whereNull('college_id')->orWhere('college_id', 0);
                })
                ->update(['college_id' => $department->college_id]);
        }

        return back()->with('success', 'Department updated successfully.');
    }

    public function destroyDepartment(Department $department)
    {
        if ($department->faculties()->exists() || $department->courses()->exists()) {
            return back()->withErrors(['department' => 'This department is linked to faculty or programs and cannot be deleted.']);
        }

        $department->delete();

        return back()->with('success', 'Department deleted successfully.');
    }

    public function allowSubject(Request $request, $facultyId)
    {
        if (!Schema::hasTable('teacher_allowed_subjects')) {
            return back()->withErrors(['subject_id' => 'Teacher qualification table is not available. Run migrations first.']);
        }

        $faculty = Faculty::findOrFail($facultyId);
        $validated = $request->validate([
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'school_year' => ['nullable', 'string'],
            'semester' => ['nullable', 'string'],
        ]);

        DB::table('teacher_allowed_subjects')->updateOrInsert([
            'faculty_id' => (int) $faculty->id,
            'subject_id' => (int) $validated['subject_id'],
        ], [
            'assigned_by_user_id' => optional($request->user())->id,
            'updated_at' => now(),
            'created_at' => now(),
        ]);

        return redirect()
            ->route($this->facultyShowRouteName($request), [
                'faculty' => (int) $faculty->id,
                'tab' => 'allowed',
                'school_year' => $validated['school_year'] ?? null,
                'semester' => $validated['semester'] ?? null,
            ])
            ->with('status', 'Allowed subject added successfully.')
            ->with('status_type', 'success');
    }

    public function removeAllowedSubject(Request $request, $facultyId, $subjectId)
    {
        if (!Schema::hasTable('teacher_allowed_subjects')) {
            return back()->withErrors(['subject_id' => 'Teacher qualification table is not available. Run migrations first.']);
        }

        $faculty = Faculty::findOrFail($facultyId);
        DB::table('teacher_allowed_subjects')
            ->where('faculty_id', (int) $faculty->id)
            ->where('subject_id', (int) $subjectId)
            ->delete();

        return redirect()
            ->route($this->facultyShowRouteName($request), [
                'faculty' => (int) $faculty->id,
                'tab' => 'allowed',
                'school_year' => $request->input('school_year'),
                'semester' => $request->input('semester'),
            ])
            ->with('status', 'Allowed subject removed successfully.')
            ->with('status_type', 'success');
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

        if (!$this->teacherQualifiedForSubject((int) $faculty->id, $subject)) {
            return redirect()
                ->route('registrar.services.classroom-faculty.faculty-loads.show', [
                    'faculty' => $faculty->id,
                    'tab' => 'loading',
                    'school_year' => $selectedSchoolYear !== '' ? $selectedSchoolYear : null,
                    'semester' => $selectedSemester !== '' ? $selectedSemester : null,
                    'loading_q' => trim((string) ($validated['loading_q'] ?? '')) ?: null,
                ])
                ->withInput()
                ->withErrors(['subject_id' => 'Selected faculty member is not qualified to teach this subject.'])
                ->with('status', 'Unable to assign subject because the faculty qualification rule failed.')
                ->with('status_type', 'danger');
        }

        $creditedTuitionUnits = array_key_exists('credited_tuition_units', $validated) && $validated['credited_tuition_units'] !== null
            ? round((float) $validated['credited_tuition_units'], 2)
            : null;

        $loadHours = array_key_exists('load_hours', $validated) && $validated['load_hours'] !== null
            ? round((float) $validated['load_hours'], 2)
            : null;

        if (!$this->teacherLoadWithinLimit((int) $faculty->id, $subject, $creditedTuitionUnits, $loadHours)) {
            return redirect()
                ->route('registrar.services.classroom-faculty.faculty-loads.show', [
                    'faculty' => $faculty->id,
                    'tab' => 'loading',
                    'school_year' => $selectedSchoolYear !== '' ? $selectedSchoolYear : null,
                    'semester' => $selectedSemester !== '' ? $selectedSemester : null,
                    'loading_q' => trim((string) ($validated['loading_q'] ?? '')) ?: null,
                ])
                ->withInput()
                ->withErrors(['subject_id' => 'Assigning this subject will exceed the faculty member maximum load.'])
                ->with('status', 'Unable to assign subject because the faculty load limit would be exceeded.')
                ->with('status_type', 'danger');
        }

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

    private function defaultFacultyLoadTermFilters(): array
    {
        if (!Schema::hasTable('academic_terms')) {
            return ['', ''];
        }

        $schoolYears = Subject::query()
            ->join('academic_terms as at', 'at.id', '=', 'subjects.academic_term_id')
            ->select('at.school_year')
            ->distinct()
            ->orderBy('at.school_year', 'desc')
            ->pluck('school_year');

        $schoolYear = (string) ($schoolYears->first() ?: '');
        if ($schoolYear === '') {
            return ['', ''];
        }

        $terms = Subject::query()
            ->join('academic_terms as at', 'at.id', '=', 'subjects.academic_term_id')
            ->where('at.school_year', $schoolYear)
            ->distinct()
            ->pluck('at.term')
            ->map(function ($term) {
                return $this->normalizeSemesterLabel((string) $term);
            })
            ->filter()
            ->unique()
            ->values();

        if ($terms->contains('Second')) {
            return [$schoolYear, 'Second'];
        }

        if ($terms->contains('First')) {
            return [$schoolYear, 'First'];
        }

        return [$schoolYear, (string) ($terms->first() ?: '')];
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

    private function teacherQualifiedForSubject(int $facultyId, Subject $subject): bool
    {
        if (!Schema::hasTable('teacher_allowed_subjects')) {
            return true;
        }

        $rows = DB::table('teacher_allowed_subjects')->where('faculty_id', $facultyId)->count();
        if ($rows === 0) {
            return true;
        }

        return DB::table('teacher_allowed_subjects')
            ->join('subjects as allowed_subjects', 'allowed_subjects.id', '=', 'teacher_allowed_subjects.subject_id')
            ->where('faculty_id', $facultyId)
            ->where(function ($query) use ($subject) {
                $query->where('teacher_allowed_subjects.subject_id', (int) $subject->id)
                    ->orWhere('allowed_subjects.code', (string) $subject->code);
            })
            ->exists();
    }

    private function teacherLoadWithinLimit(int $facultyId, Subject $subject, ?float $incomingCreditedUnits = null, ?float $incomingLoadHours = null): bool
    {
        $max = $this->teacherMaxLoadUnits($facultyId);
        if ($max <= 0) {
            return true;
        }

        $current = (float) Subject::query()
            ->where('faculty_id', $facultyId)
            ->where('id', '<>', (int) $subject->id)
            ->when((int) $subject->academic_term_id > 0, function ($query) use ($subject) {
                $query->where('academic_term_id', (int) $subject->academic_term_id);
            })
            ->sum(DB::raw($this->facultyLoadSqlExpression()));

        $incoming = $incomingCreditedUnits !== null
            ? (float) $incomingCreditedUnits
            : $this->facultyLoadValue($subject);

        return ($current + $incoming) <= $max;
    }

    private function facultyLoadValue(Subject $subject): float
    {
        if ($subject->credited_tuition_units !== null && $subject->credited_tuition_units !== '') {
            return (float) $subject->credited_tuition_units;
        }

        return 0.0;
    }

    private function computedFacultyLoadSummary(int $facultyId, float $currentLoad, int $subjectCount = 0): array
    {
        $maxLoad = $this->teacherMaxLoadUnits($facultyId);
        $remainingLoad = $maxLoad - $currentLoad;
        $status = 'Underload';

        if ($currentLoad > $maxLoad) {
            $status = 'Overload';
        } elseif (abs($currentLoad - $maxLoad) < 0.01) {
            $status = 'Full Load';
        }

        return [
            'current_load' => $currentLoad,
            'subject_count' => $subjectCount,
            'max_load' => $maxLoad,
            'remaining_load' => $remainingLoad,
            'status' => $status,
        ];
    }

    private function facultyLoadSqlExpression(): string
    {
        $parts = [];

        if (Schema::hasColumn('subjects', 'credited_tuition_units')) {
            $parts[] = 'credited_tuition_units';
        }

        return $parts ? 'COALESCE(' . implode(', ', $parts) . ', 0)' : '0';
    }

    private function teacherMaxLoadUnits(int $facultyId): float
    {
        $faculty = Faculty::query()->find($facultyId);
        if (!$faculty) {
            return 0.0;
        }

        if (Schema::hasColumn('faculties', 'max_load_units') && (float) ($faculty->max_load_units ?? 0) > 0) {
            return (float) $faculty->max_load_units;
        }

        $type = Schema::hasColumn('faculties', 'employment_type')
            ? (string) ($faculty->employment_type ?: 'Full-time Teacher')
            : 'Full-time Teacher';

        if (Schema::hasTable('teacher_load_settings')) {
            $configured = DB::table('teacher_load_settings')
                ->where('employment_type', $type)
                ->value('max_load_units');
            if ($configured !== null) {
                return (float) $configured;
            }
        }

        $defaults = [
            'Full-time Teacher' => 24,
            'Part-time Teacher' => 12,
            'Department Head' => 9,
            'Visiting Lecturer' => 6,
        ];

        return (float) ($defaults[$type] ?? 24);
    }

    private function facultyProfileRow(Faculty $faculty)
    {
        if (!Schema::hasTable('master_faculty_files')) {
            return null;
        }

        if (Schema::hasColumn('master_faculty_files', 'source_faculty_id')) {
            $row = MasterFacultyFile::query()
                ->where('source_faculty_id', (int) $faculty->id)
                ->first();
            if ($row) {
                return $row;
            }
        }

        $code = trim((string) $faculty->code);
        if ($code !== '') {
            $row = MasterFacultyFile::query()->where('code', $code)->first();
            if ($row) {
                return $row;
            }
        }

        $name = trim((string) $faculty->name);
        return $name !== '' ? MasterFacultyFile::query()->where('name', $name)->first() : null;
    }

    private function qualifiedSubjectsForFaculty(int $facultyId)
    {
        if (!Schema::hasTable('teacher_allowed_subjects')) {
            return collect();
        }

        return Subject::query()
            ->join('teacher_allowed_subjects as tas', 'tas.subject_id', '=', 'subjects.id')
            ->where('tas.faculty_id', $facultyId)
            ->orderBy('subjects.code')
            ->orderBy('subjects.name')
            ->get(['subjects.id', 'subjects.code', 'subjects.name', 'subjects.units', 'subjects.lec', 'subjects.lab', 'subjects.course_type']);
    }

    private function facultyShowRouteName(Request $request): string
    {
        $routeName = optional($request->route())->getName();

        if (is_string($routeName) && strpos($routeName, 'registrar.registrar-menu.faculty-mgmt.') === 0) {
            return 'registrar.registrar-menu.faculty-mgmt.faculty-list.show';
        }

        return 'registrar.services.classroom-faculty.faculty-loads.show';
    }
}
