<?php

namespace App\Http\Controllers\Registrar\Services;

use App\Http\Controllers\Controller;
use App\AcademicTerm;
use App\GradeRule;
use App\GradingComponent;
use App\GradingPeriod;
use App\Course;
use App\Student;
use App\StudentDeficiency;
use App\TransmutationRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class GradingAcademicController extends Controller
{
    public function gradingSystem()
    {
        $gradeRulesQuery = GradeRule::query();
        if (Schema::hasTable('grade_rule_periods')) {
            $gradeRulesQuery->with('periodItems');
        }

        $gradeRules = $gradeRulesQuery->orderBy('code')->get();

        return view('registrar.services.grading-academic.grading-system', compact('gradeRules'));
    }

    public function gradingSystemStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:grade_rules,code',
            'grade' => 'nullable|string|max:20',
            'remarks' => 'required|string|max:255',
            'periods' => 'nullable|array',
            'periods.*' => 'nullable|string|max:50',
        ]);

        $rule = GradeRule::create([
            'code' => strtoupper(trim($validated['code'])),
            'grade' => isset($validated['grade']) ? trim((string) $validated['grade']) : null,
            'remarks' => trim($validated['remarks']),
        ]);

        $rule->syncPeriods(isset($validated['periods']) ? array_values($validated['periods']) : []);

        return response()->json(['ok' => true, 'id' => $rule->id]);
    }

    public function gradingSystemUpdate(Request $request, GradeRule $gradeRule): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:grade_rules,code,' . $gradeRule->id,
            'grade' => 'nullable|string|max:20',
            'remarks' => 'required|string|max:255',
            'periods' => 'nullable|array',
            'periods.*' => 'nullable|string|max:50',
        ]);

        $gradeRule->update([
            'code' => strtoupper(trim($validated['code'])),
            'grade' => isset($validated['grade']) ? trim((string) $validated['grade']) : null,
            'remarks' => trim($validated['remarks']),
        ]);

        if (array_key_exists('periods', $validated)) {
            $gradeRule->syncPeriods(array_values((array) $validated['periods']));
        }

        return response()->json(['ok' => true]);
    }

    public function gradingSystemDestroy(GradeRule $gradeRule): JsonResponse
    {
        $gradeRule->delete();

        return response()->json(['ok' => true]);
    }

    public function gradingPeriods()
    {
        $gradingPeriods = GradingPeriod::query()
            ->leftJoin('academic_terms as at', 'at.id', '=', 'grading_periods.academic_term_id')
            ->select('grading_periods.*')
            ->orderByDesc('at.school_year')
            ->orderBy('at.term')
            ->orderBy('period')
            ->get();

        return view('registrar.services.grading-academic.grading-periods', compact('gradingPeriods'));
    }

    public function gradingPeriodsStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'section_subject_faculty' => 'required|string|max:150',
            'period' => 'required|string|max:30',
            'description' => 'required|string|max:120',
            'percentage' => 'required|numeric|min:0|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'grading_computation' => 'required|string|max:50',
            'use_grades_library' => 'nullable|boolean',
            'school_year' => 'nullable|string|max:20',
            'semester' => 'nullable|string|max:20',
        ]);

        $record = GradingPeriod::create([
            'section_subject_faculty' => trim($validated['section_subject_faculty']),
            'period' => trim($validated['period']),
            'description' => trim($validated['description']),
            'percentage' => (float) $validated['percentage'],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'start_time' => $validated['start_time'] ?? null,
            'grading_computation' => trim($validated['grading_computation']),
            'use_grades_library' => (bool) ($validated['use_grades_library'] ?? false),
            'school_year' => isset($validated['school_year']) ? trim($validated['school_year']) : null,
            'semester' => isset($validated['semester']) ? trim($validated['semester']) : null,
        ]);

        return response()->json(['ok' => true, 'id' => $record->id]);
    }

    public function gradingPeriodsUpdate(Request $request, GradingPeriod $gradingPeriod): JsonResponse
    {
        $validated = $request->validate([
            'section_subject_faculty' => 'required|string|max:150',
            'period' => 'required|string|max:30',
            'description' => 'required|string|max:120',
            'percentage' => 'required|numeric|min:0|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'grading_computation' => 'required|string|max:50',
            'use_grades_library' => 'nullable|boolean',
            'school_year' => 'nullable|string|max:20',
            'semester' => 'nullable|string|max:20',
        ]);

        $gradingPeriod->update([
            'section_subject_faculty' => trim($validated['section_subject_faculty']),
            'period' => trim($validated['period']),
            'description' => trim($validated['description']),
            'percentage' => (float) $validated['percentage'],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'start_time' => $validated['start_time'] ?? null,
            'grading_computation' => trim($validated['grading_computation']),
            'use_grades_library' => (bool) ($validated['use_grades_library'] ?? false),
            'school_year' => isset($validated['school_year']) ? trim($validated['school_year']) : null,
            'semester' => isset($validated['semester']) ? trim($validated['semester']) : null,
        ]);

        return response()->json(['ok' => true]);
    }

    public function gradingPeriodsDestroy(GradingPeriod $gradingPeriod): JsonResponse
    {
        $gradingPeriod->delete();

        return response()->json(['ok' => true]);
    }

    public function gradingComponents()
    {
        $gradingComponents = GradingComponent::query()
            ->leftJoin('academic_terms as at', 'at.id', '=', 'grading_components.academic_term_id')
            ->select('grading_components.*')
            ->orderByDesc('at.school_year')
            ->orderBy('at.term')
            ->orderBy('period')
            ->orderBy('sequence_no')
            ->get();

        return view('registrar.services.grading-academic.grading-components', compact('gradingComponents'));
    }

    public function gradingComponentsStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'school_year' => 'required|string|max:20',
            'period' => 'required|string|max:30',
            'semester' => 'required|string|max:20',
            'section' => 'required|string|max:40',
            'course_code' => 'required|string|max:40',
            'title' => 'required|string|max:120',
            'sequence_no' => 'required|integer|min:1|max:999',
            'percentage' => 'required|numeric|min:0|max:100',
            'lab_mode' => 'nullable|string|max:20',
            'cap' => 'nullable|string|max:20',
            'updated_by' => 'nullable|string|max:80',
            'effective_date' => 'nullable|date',
        ]);

        $record = GradingComponent::create([
            'school_year' => trim($validated['school_year']),
            'period' => trim($validated['period']),
            'semester' => trim($validated['semester']),
            'section' => trim($validated['section']),
            'course_code' => trim($validated['course_code']),
            'title' => trim($validated['title']),
            'sequence_no' => (int) $validated['sequence_no'],
            'percentage' => (float) $validated['percentage'],
            'lab_mode' => isset($validated['lab_mode']) ? trim($validated['lab_mode']) : null,
            'cap' => isset($validated['cap']) ? trim($validated['cap']) : null,
            'updated_by' => isset($validated['updated_by']) ? trim($validated['updated_by']) : 'Registrar',
            'effective_date' => $validated['effective_date'] ?? null,
        ]);

        return response()->json(['ok' => true, 'id' => $record->id]);
    }

    public function gradingComponentsUpdate(Request $request, GradingComponent $gradingComponent): JsonResponse
    {
        $validated = $request->validate([
            'school_year' => 'required|string|max:20',
            'period' => 'required|string|max:30',
            'semester' => 'required|string|max:20',
            'section' => 'required|string|max:40',
            'course_code' => 'required|string|max:40',
            'title' => 'required|string|max:120',
            'sequence_no' => 'required|integer|min:1|max:999',
            'percentage' => 'required|numeric|min:0|max:100',
            'lab_mode' => 'nullable|string|max:20',
            'cap' => 'nullable|string|max:20',
            'updated_by' => 'nullable|string|max:80',
            'effective_date' => 'nullable|date',
        ]);

        $gradingComponent->update([
            'school_year' => trim($validated['school_year']),
            'period' => trim($validated['period']),
            'semester' => trim($validated['semester']),
            'section' => trim($validated['section']),
            'course_code' => trim($validated['course_code']),
            'title' => trim($validated['title']),
            'sequence_no' => (int) $validated['sequence_no'],
            'percentage' => (float) $validated['percentage'],
            'lab_mode' => isset($validated['lab_mode']) ? trim($validated['lab_mode']) : null,
            'cap' => isset($validated['cap']) ? trim($validated['cap']) : null,
            'updated_by' => isset($validated['updated_by']) ? trim($validated['updated_by']) : 'Registrar',
            'effective_date' => $validated['effective_date'] ?? null,
        ]);

        return response()->json(['ok' => true]);
    }

    public function gradingComponentsDestroy(GradingComponent $gradingComponent): JsonResponse
    {
        $gradingComponent->delete();

        return response()->json(['ok' => true]);
    }

    public function transmutation(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $transmutationRules = TransmutationRule::query()
            ->leftJoin('academic_terms as at', 'at.id', '=', 'transmutation_rules.academic_term_id')
            ->leftJoin('courses as c', 'c.id', '=', 'transmutation_rules.course_id')
            ->with(['academicTerm:id,school_year,term', 'canonicalCourse:id,code,name'])
            ->select('transmutation_rules.*')
            ->when($search !== '', function ($query) use ($search) {
                $like = '%' . $search . '%';

                $query->where(function ($inner) use ($like) {
                    $inner->where('at.school_year', 'like', $like)
                        ->orWhere('at.term', 'like', $like)
                        ->orWhere('c.code', 'like', $like)
                        ->orWhere('c.name', 'like', $like)
                        ->orWhere('transmutation_rules.code', 'like', $like)
                        ->orWhere('transmutation_rules.remarks', 'like', $like)
                        ->orWhereRaw('CAST(transmutation_rules.initial_from AS CHAR) like ?', [$like])
                        ->orWhereRaw('CAST(transmutation_rules.initial_to AS CHAR) like ?', [$like])
                        ->orWhereRaw('CAST(transmutation_rules.transmuted_grade AS CHAR) like ?', [$like]);
                });
            })
            ->orderByDesc('at.school_year')
            ->orderByRaw($this->transmutationTermSortSql('at.term'))
            ->orderByRaw("COALESCE(NULLIF(c.code, ''), c.name) ASC")
            ->orderByDesc('transmutation_rules.initial_from')
            ->paginate(25)
            ->appends($request->except('page'));

        $transmutationRules->getCollection()->transform(function ($rule) {
            $academicTerm = $rule->relationLoaded('academicTerm') ? $rule->academicTerm : null;
            $canonicalCourse = $rule->relationLoaded('canonicalCourse') ? $rule->canonicalCourse : null;

            $rule->resolved_school_year = trim((string) optional($academicTerm)->school_year);
            $rule->resolved_term = $this->canonicalTransmutationTermLabel(optional($academicTerm)->term);
            $rule->resolved_program = trim((string) (optional($canonicalCourse)->code ?: optional($canonicalCourse)->name));

            return $rule;
        });

        $schoolYearOptions = AcademicTerm::query()
            ->whereNotNull('school_year')
            ->where('school_year', '<>', '')
            ->orderBy('school_year', 'desc')
            ->pluck('school_year')
            ->unique()
            ->map(function ($schoolYear) {
                return [
                    'value' => (string) $schoolYear,
                    'label' => (string) $schoolYear,
                ];
            })
            ->values()
            ->all();

        array_unshift($schoolYearOptions, [
            'value' => '',
            'label' => 'Select school year',
        ]);

        $termOptions = [
            ['value' => '', 'label' => 'Select term'],
            ['value' => 'First', 'label' => 'First'],
            ['value' => 'Second', 'label' => 'Second'],
            ['value' => 'Summer', 'label' => 'Summer'],
        ];

        $programOptions = Course::query()
            ->select('id', 'code', 'name')
            ->where(function ($query) {
                $query->whereNotNull('code')->where('code', '<>', '')
                    ->orWhere(function ($inner) {
                        $inner->whereNotNull('name')->where('name', '<>', '');
                    });
            })
            ->orderByRaw("COALESCE(NULLIF(code, ''), name) ASC")
            ->get()
            ->map(function ($course) {
                $courseCode = trim((string) $course->code);
                $courseName = trim((string) $course->name);

                if ($courseCode !== '' && $courseName !== '') {
                    $label = $courseCode . ' - ' . $courseName;
                } else {
                    $label = $courseCode !== '' ? $courseCode : $courseName;
                }

                return [
                    'value' => (string) $course->id,
                    'label' => $label,
                ];
            })
            ->values()
            ->all();

        array_unshift($programOptions, [
            'value' => '',
            'label' => 'Select program',
        ]);

        return view('registrar.services.grading-academic.transmutation', [
            'transmutationRules' => $transmutationRules,
            'search' => $search,
            'schoolYearOptions' => $schoolYearOptions,
            'termOptions' => $termOptions,
            'programOptions' => $programOptions,
        ]);
    }

    public function transmutationStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'school_year' => 'required|string|max:20|exists:academic_terms,school_year',
            'term' => 'required|string|in:First,Second,Summer',
            'course_id' => 'required|integer|exists:courses,id',
            'initial_from' => 'required|numeric',
            'initial_to' => 'required|numeric|gte:initial_from',
            'transmuted_grade' => 'required|numeric',
            'code' => 'required|string|max:10',
            'remarks' => 'required|string|max:100',
        ]);

        $academicTermId = $this->resolveAcademicTermId($validated['school_year'], $validated['term']);
        if (!$academicTermId) {
            return response()->json([
                'message' => 'The selected school year and term combination is invalid.',
                'errors' => [
                    'term' => ['The selected term is not available for the selected school year.'],
                ],
            ], 422);
        }

        $payload = [
            'academic_term_id' => $academicTermId,
            'course_id' => (int) $validated['course_id'],
            'initial_from' => (float) $validated['initial_from'],
            'initial_to' => (float) $validated['initial_to'],
            'transmuted_grade' => (float) $validated['transmuted_grade'],
            'code' => strtoupper(trim($validated['code'])),
            'remarks' => trim($validated['remarks']),
        ];

        $rule = TransmutationRule::create($payload);

        return response()->json(['ok' => true, 'id' => $rule->id]);
    }

    public function transmutationUpdate(Request $request, TransmutationRule $transmutationRule): JsonResponse
    {
        $validated = $request->validate([
            'school_year' => 'required|string|max:20|exists:academic_terms,school_year',
            'term' => 'required|string|in:First,Second,Summer',
            'course_id' => 'required|integer|exists:courses,id',
            'initial_from' => 'required|numeric',
            'initial_to' => 'required|numeric|gte:initial_from',
            'transmuted_grade' => 'required|numeric',
            'code' => 'required|string|max:10',
            'remarks' => 'required|string|max:100',
        ]);

        $academicTermId = $this->resolveAcademicTermId($validated['school_year'], $validated['term']);
        if (!$academicTermId) {
            return response()->json([
                'message' => 'The selected school year and term combination is invalid.',
                'errors' => [
                    'term' => ['The selected term is not available for the selected school year.'],
                ],
            ], 422);
        }

        $payload = [
            'academic_term_id' => $academicTermId,
            'course_id' => (int) $validated['course_id'],
            'initial_from' => (float) $validated['initial_from'],
            'initial_to' => (float) $validated['initial_to'],
            'transmuted_grade' => (float) $validated['transmuted_grade'],
            'code' => strtoupper(trim($validated['code'])),
            'remarks' => trim($validated['remarks']),
        ];

        $transmutationRule->update($payload);

        return response()->json(['ok' => true]);
    }

    public function transmutationDestroy(TransmutationRule $transmutationRule): JsonResponse
    {
        $transmutationRule->delete();

        return response()->json(['ok' => true]);
    }

    public function deficiency()
    {
        $search = trim((string) request()->query('q', ''));
        $hasProgramColumn = Schema::hasColumn('students', 'program');
        $hasYearLevelColumn = Schema::hasColumn('students', 'year_level');
        $hasCourseCodeColumn = Schema::hasTable('courses') && Schema::hasColumn('courses', 'code');
        $hasCourseNameColumn = Schema::hasTable('courses') && Schema::hasColumn('courses', 'name');
        $hasYearBlockLabelColumn = Schema::hasTable('year_blocks') && Schema::hasColumn('year_blocks', 'label');

        $students = Student::query()
            ->with(['canonicalCourse:id,code,name', 'yearBlock:id,label'])
            ->select('students.*')
            ->when($search !== '', function ($query) use (
                $search,
                $hasProgramColumn,
                $hasYearLevelColumn,
                $hasCourseCodeColumn,
                $hasCourseNameColumn,
                $hasYearBlockLabelColumn
            ) {
                $like = '%' . $search . '%';

                $query->where(function ($inner) use (
                    $like,
                    $hasProgramColumn,
                    $hasYearLevelColumn,
                    $hasCourseCodeColumn,
                    $hasCourseNameColumn,
                    $hasYearBlockLabelColumn
                ) {
                    $inner->where('students.student_no', 'like', $like)
                        ->orWhere('students.name', 'like', $like);

                    if ($hasProgramColumn) {
                        $inner->orWhere('students.program', 'like', $like);
                    }

                    if ($hasYearLevelColumn) {
                        $inner->orWhere('students.year_level', 'like', $like);
                    }

                    if ($hasCourseCodeColumn || $hasCourseNameColumn) {
                        $inner->orWhereHas('canonicalCourse', function ($courseQuery) use ($like, $hasCourseCodeColumn, $hasCourseNameColumn) {
                            $courseQuery->where(function ($courseInner) use ($like, $hasCourseCodeColumn, $hasCourseNameColumn) {
                                if ($hasCourseCodeColumn) {
                                    $courseInner->where('courses.code', 'like', $like);
                                }

                                if ($hasCourseNameColumn) {
                                    if ($hasCourseCodeColumn) {
                                        $courseInner->orWhere('courses.name', 'like', $like);
                                    } else {
                                        $courseInner->where('courses.name', 'like', $like);
                                    }
                                }
                            });
                        });
                    }

                    if ($hasYearBlockLabelColumn) {
                        $inner->orWhereHas('yearBlock', function ($yearBlockQuery) use ($like) {
                            $yearBlockQuery->where('year_blocks.label', 'like', $like);
                        });
                    }
                });
            })
            ->orderByDesc('students.id')
            ->paginate(25)
            ->appends(request()->except('page'));

        $students->getCollection()->transform(function ($student) {
            $canonicalCourse = $student->relationLoaded('canonicalCourse') ? $student->canonicalCourse : null;
            $yearBlock = $student->relationLoaded('yearBlock') ? $student->yearBlock : null;

            $student->resolved_program = trim((string) ($student->program ?: (optional($canonicalCourse)->code ?: optional($canonicalCourse)->name)));
            $student->resolved_year_level = trim((string) ($student->year_level ?: optional($yearBlock)->label));

            return $student;
        });

        $selectedStudent = $students->first();
        $studentDeficiencies = collect();
        $currentUserName = optional(request()->user())->name ?: 'Registrar';

        if ($selectedStudent) {
            $studentDeficiencies = StudentDeficiency::query()
                ->where('student_id', $selectedStudent->id)
                ->orderByDesc('submission_date')
                ->orderBy('department')
                ->get();
        }

        return view('registrar.services.grading-academic.deficiency', compact('students', 'studentDeficiencies', 'selectedStudent', 'search', 'currentUserName'));
    }

    public function deficiencyRecords(Student $student): JsonResponse
    {
        $records = StudentDeficiency::query()
            ->where('student_id', $student->id)
            ->orderByDesc('submission_date')
            ->orderBy('department')
            ->get();

        return response()->json(['ok' => true, 'data' => $records]);
    }

    public function deficiencyStudentSearch(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('q', ''));
        $limit = max(1, min(20, (int) $request->query('limit', 12)));

        $students = Student::query()
            ->select('id', 'student_no', 'name')
            ->whereNotNull('student_no')
            ->where('student_no', '<>', '')
            ->whereNotNull('name')
            ->where('name', '<>', '')
            ->when($search !== '', function ($query) use ($search) {
                $like = '%' . $search . '%';

                $query->where(function ($inner) use ($like) {
                    $inner->where('student_no', 'like', $like)
                        ->orWhere('name', 'like', $like);
                });
            })
            ->orderBy('student_no')
            ->limit($limit)
            ->get();

        return response()->json([
            'ok' => true,
            'data' => $students,
        ]);
    }

    public function deficiencyStudentStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'student_no' => 'required|string|max:40',
            'name' => 'required|string|max:120',
        ]);

        $student = Student::query()->select('id', 'student_no', 'name')->find($validated['student_id']);
        if (!$student) {
            return response()->json([
                'message' => 'Selected student is not available.',
                'errors' => [
                    'student_id' => ['Please select a valid student from the search results.'],
                ],
            ], 422);
        }

        $studentNo = trim((string) $validated['student_no']);
        $name = trim((string) $validated['name']);
        $matchesStudentNo = strcasecmp(trim((string) $student->student_no), $studentNo) === 0;
        $matchesStudentName = strcasecmp(trim((string) $student->name), $name) === 0;

        if (!$matchesStudentNo || !$matchesStudentName) {
            return response()->json([
                'message' => 'Selected student details do not match the current input.',
                'errors' => [
                    'student_id' => ['Please select a student from the list and do not edit the values manually.'],
                ],
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'data' => [
                'id' => $student->id,
                'student_no' => $student->student_no,
                'name' => $student->name,
            ],
        ]);
    }

    public function deficiencyStore(Request $request, Student $student): JsonResponse
    {
        $validated = $request->validate([
            'department' => 'required|string|max:80',
            'remarks' => 'required|string|max:255',
            'date_today' => 'nullable|date',
            'submission_date' => 'nullable|date',
            'is_completed' => 'nullable|boolean',
            'compliance_date' => 'nullable|date',
        ]);

        $updatedBy = optional($request->user())->name ?: 'Registrar';

        $record = StudentDeficiency::create([
            'student_id' => $student->id,
            'department' => trim($validated['department']),
            'remarks' => trim($validated['remarks']),
            'date_today' => $validated['date_today'] ?? null,
            'submission_date' => $validated['submission_date'] ?? null,
            'is_completed' => (bool) ($validated['is_completed'] ?? false),
            'compliance_date' => $validated['compliance_date'] ?? null,
            'updated_by' => $updatedBy,
        ]);

        return response()->json(['ok' => true, 'id' => $record->id]);
    }

    public function deficiencyUpdate(Request $request, StudentDeficiency $studentDeficiency): JsonResponse
    {
        $validated = $request->validate([
            'department' => 'required|string|max:80',
            'remarks' => 'required|string|max:255',
            'date_today' => 'nullable|date',
            'submission_date' => 'nullable|date',
            'is_completed' => 'nullable|boolean',
            'compliance_date' => 'nullable|date',
        ]);

        $updatedBy = optional($request->user())->name ?: 'Registrar';

        $studentDeficiency->update([
            'department' => trim($validated['department']),
            'remarks' => trim($validated['remarks']),
            'date_today' => $validated['date_today'] ?? null,
            'submission_date' => $validated['submission_date'] ?? null,
            'is_completed' => (bool) ($validated['is_completed'] ?? false),
            'compliance_date' => $validated['compliance_date'] ?? null,
            'updated_by' => $updatedBy,
        ]);

        return response()->json(['ok' => true]);
    }

    public function deficiencyDestroy(StudentDeficiency $studentDeficiency): JsonResponse
    {
        $studentDeficiency->delete();

        return response()->json(['ok' => true]);
    }

    private function resolveAcademicTermId($schoolYear, $canonicalTerm)
    {
        $schoolYear = trim((string) $schoolYear);
        $canonicalTerm = $this->canonicalTransmutationTermLabel($canonicalTerm);

        if ($schoolYear === '' || $canonicalTerm === '') {
            return null;
        }

        return AcademicTerm::query()
            ->where('school_year', $schoolYear)
            ->whereIn('term', $this->transmutationTermAliases($canonicalTerm))
            ->value('id');
    }

    private function canonicalTransmutationTermLabel($value)
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

        return trim((string) $value);
    }

    private function transmutationTermAliases($canonicalLabel)
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

    private function transmutationTermSortSql($qualifiedColumn)
    {
        return "CASE
            WHEN LOWER(" . $qualifiedColumn . ") LIKE '%first%' OR LOWER(" . $qualifiedColumn . ") LIKE '%1st%' THEN 1
            WHEN LOWER(" . $qualifiedColumn . ") LIKE '%second%' OR LOWER(" . $qualifiedColumn . ") LIKE '%2nd%' THEN 2
            WHEN LOWER(" . $qualifiedColumn . ") LIKE '%summer%' THEN 3
            ELSE 4
        END";
    }
}
