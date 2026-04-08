<?php

namespace App\Http\Controllers\Registrar\Services;

use App\Http\Controllers\Controller;
use App\GradeRule;
use App\GradingComponent;
use App\GradingPeriod;
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
            ->orderByDesc('school_year')
            ->orderBy('semester')
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
            ->orderByDesc('school_year')
            ->orderBy('semester')
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

    public function transmutation()
    {
        $transmutationRules = TransmutationRule::query()
            ->orderByDesc('school_year')
            ->orderBy('program')
            ->orderByDesc('initial_from')
            ->get();

        return view('registrar.services.grading-academic.transmutation', compact('transmutationRules'));
    }

    public function transmutationStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'school_year' => 'required|string|max:20',
            'term' => 'required|string|max:20',
            'program' => 'required|string|max:50',
            'initial_from' => 'required|numeric',
            'initial_to' => 'required|numeric|gte:initial_from',
            'transmuted_grade' => 'required|numeric',
            'code' => 'required|string|max:10',
            'remarks' => 'required|string|max:100',
        ]);

        $rule = TransmutationRule::create($validated);

        return response()->json(['ok' => true, 'id' => $rule->id]);
    }

    public function transmutationUpdate(Request $request, TransmutationRule $transmutationRule): JsonResponse
    {
        $validated = $request->validate([
            'school_year' => 'required|string|max:20',
            'term' => 'required|string|max:20',
            'program' => 'required|string|max:50',
            'initial_from' => 'required|numeric',
            'initial_to' => 'required|numeric|gte:initial_from',
            'transmuted_grade' => 'required|numeric',
            'code' => 'required|string|max:10',
            'remarks' => 'required|string|max:100',
        ]);

        $transmutationRule->update($validated);

        return response()->json(['ok' => true]);
    }

    public function transmutationDestroy(TransmutationRule $transmutationRule): JsonResponse
    {
        $transmutationRule->delete();

        return response()->json(['ok' => true]);
    }

    public function deficiency()
    {
        $students = Student::query()
            ->orderBy('name')
            ->get(['id', 'student_no', 'name', 'program', 'year_level']);

        $selectedStudent = $students->first();
        $studentDeficiencies = collect();

        if ($selectedStudent) {
            $studentDeficiencies = StudentDeficiency::query()
                ->where('student_id', $selectedStudent->id)
                ->orderByDesc('submission_date')
                ->orderBy('department')
                ->get();
        }

        return view('registrar.services.grading-academic.deficiency', compact('students', 'studentDeficiencies', 'selectedStudent'));
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

    public function deficiencyStudentStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_no' => 'required|string|max:40',
            'name' => 'required|string|max:120',
        ]);

        $studentNo = trim($validated['student_no']);
        $name = trim($validated['name']);

        $student = Student::query()->where('student_no', $studentNo)->first();

        if ($student) {
            if (trim((string) $student->name) !== $name) {
                $student->name = $name;
                $student->save();
            }
        } else {
            $student = Student::create([
                'student_no' => $studentNo,
                'name' => $name,
                'program' => null,
                'year_level' => null,
                'school_year' => '2025-2026',
                'semester' => 'First',
            ]);
        }

        return response()->json([
            'ok' => true,
            'data' => [
                'id' => $student->id,
                'student_no' => $student->student_no,
                'name' => $student->name,
                'program' => $student->program,
                'year_level' => $student->year_level,
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
            'updated_by' => 'nullable|string|max:80',
        ]);

        $record = StudentDeficiency::create([
            'student_id' => $student->id,
            'department' => trim($validated['department']),
            'remarks' => trim($validated['remarks']),
            'date_today' => $validated['date_today'] ?? null,
            'submission_date' => $validated['submission_date'] ?? null,
            'is_completed' => (bool) ($validated['is_completed'] ?? false),
            'compliance_date' => $validated['compliance_date'] ?? null,
            'updated_by' => isset($validated['updated_by']) ? trim($validated['updated_by']) : null,
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
            'updated_by' => 'nullable|string|max:80',
        ]);

        $studentDeficiency->update([
            'department' => trim($validated['department']),
            'remarks' => trim($validated['remarks']),
            'date_today' => $validated['date_today'] ?? null,
            'submission_date' => $validated['submission_date'] ?? null,
            'is_completed' => (bool) ($validated['is_completed'] ?? false),
            'compliance_date' => $validated['compliance_date'] ?? null,
            'updated_by' => isset($validated['updated_by']) ? trim($validated['updated_by']) : null,
        ]);

        return response()->json(['ok' => true]);
    }

    public function deficiencyDestroy(StudentDeficiency $studentDeficiency): JsonResponse
    {
        $studentDeficiency->delete();

        return response()->json(['ok' => true]);
    }
}
