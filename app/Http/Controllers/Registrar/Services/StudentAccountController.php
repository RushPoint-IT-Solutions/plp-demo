<?php

namespace App\Http\Controllers\Registrar\Services;

use App\AcademicTerm;
use App\Course;
use App\StudentProfile;
use App\StudentDisciplineActionType;
use App\StudentDisciplineCaseType;
use App\StudentDisciplineRecord;
use App\StudentDisciplineStudent;
use App\StudentDisciplineStudentType;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StudentAccountController extends Controller
{
    public function studentDiscipline(Request $request)
    {
        $schoolYears = AcademicTerm::query()
            ->select('school_year')
            ->distinct()
            ->orderBy('school_year', 'desc')
            ->pluck('school_year')
            ->values()
            ->all();

        if (!count($schoolYears)) {
            $schoolYears = ['2025-2026'];
        }

        $defaultSchoolYear = trim((string) $request->query('school_year', ''));
        if ($defaultSchoolYear === '' || !in_array($defaultSchoolYear, $schoolYears, true)) {
            $defaultSchoolYear = $schoolYears[0];
        }

        $termOptions = [
            ['value' => 'First', 'label' => 'First'],
            ['value' => 'Second', 'label' => 'Second'],
            ['value' => 'Summer', 'label' => 'Summer'],
        ];

        $defaultTerm = trim((string) $request->query('term', 'First'));
        if (!in_array($defaultTerm, ['First', 'Second', 'Summer'], true)) {
            $defaultTerm = 'First';
        }

        $studentTypeOptions = $this->disciplineStudentTypeOptions();
        $caseTypeOptions = $this->disciplineCaseTypeOptions();
        $actionTypeOptions = $this->disciplineActionTypeOptions();

        return view('registrar.services.student-account.student-discipline', compact(
            'schoolYears',
            'defaultSchoolYear',
            'termOptions',
            'defaultTerm',
            'studentTypeOptions',
            'caseTypeOptions',
            'actionTypeOptions'
        ));
    }

    public function studentDisciplineData(Request $request): JsonResponse
    {
        if (!$this->disciplineTablesReady()) {
            return response()->json([
                'ok' => true,
                'data' => [],
                'total' => 0,
            ]);
        }

        $studentNo = trim((string) $request->query('student_id', ''));
        $fullName = trim((string) $request->query('full_name', ''));
        $studentType = strtoupper(trim((string) $request->query('student_type', '')));
        $schoolYear = trim((string) $request->query('school_year', ''));
        $term = trim((string) $request->query('term', ''));

        $query = DB::table('student_discipline_students as sds')
            ->join('students as s', 's.id', '=', 'sds.student_id')
            ->leftJoin('courses as c', 'c.id', '=', 'sds.course_id')
            ->leftJoin('student_profiles as sp', 'sp.student_id', '=', 's.id')
            ->leftJoin('student_discipline_student_types as st', 'st.id', '=', 'sds.student_type_id')
            ->leftJoin('academic_terms as at', 'at.id', '=', 's.academic_term_id')
            ->select(
                'sds.id as discipline_student_id',
                's.id as student_id',
                's.student_no',
                's.name as student_name',
                's.sex as student_sex',
                'sds.course_id',
                'c.code as course_code',
                'c.name as course_name',
                'sp.date_of_birth',
                'sp.gender as profile_gender',
                'st.code as student_type_code',
                'st.label as student_type_label'
            )
            ->when($studentNo !== '', function ($builder) use ($studentNo) {
                $builder->where('s.student_no', 'like', '%' . $studentNo . '%');
            })
            ->when($fullName !== '', function ($builder) use ($fullName) {
                $builder->where('s.name', 'like', '%' . $fullName . '%');
            })
            ->when($studentType !== '', function ($builder) use ($studentType) {
                $builder->where('st.code', $studentType);
            })
            ->when($schoolYear !== '', function ($builder) use ($schoolYear) {
                $builder->where('at.school_year', $schoolYear);
            });

        $termAliases = $this->disciplineTermAliases($term);
        if (count($termAliases)) {
            $query->where(function ($inner) use ($termAliases) {
                foreach ($termAliases as $index => $alias) {
                    if ($index === 0) {
                        $inner->where('at.term', $alias);
                    } else {
                        $inner->orWhere('at.term', $alias);
                    }
                }
            });
        }

        $rows = $query
            ->orderBy('s.name')
            ->orderBy('s.student_no')
            ->get();

        $payload = $rows->map(function ($row) {
            $degreeProgram = trim((string) ($row->course_name ?: $row->course_code));
            if ($degreeProgram === '') {
                $degreeProgram = '-';
            }

            $gender = $this->disciplineNormalizeGenderLabel($row->profile_gender ?: $row->student_sex);

            $studentTypeLabel = trim((string) $row->student_type_label);
            if ($studentTypeLabel === '') {
                $studentTypeLabel = '-';
            }

            $birthDate = '-';
            $birthDateIso = null;
            if (!empty($row->date_of_birth)) {
                $birthDateIso = Carbon::parse($row->date_of_birth)->format('Y-m-d');
                $birthDate = Carbon::parse($row->date_of_birth)->format('m/d/Y');
            }

            return [
                'discipline_student_id' => (int) $row->discipline_student_id,
                'student_id' => (int) $row->student_id,
                'student_no' => (string) $row->student_no,
                'name' => (string) $row->student_name,
                'degree_program' => $degreeProgram,
                'birth_date' => $birthDate,
                'birth_date_iso' => $birthDateIso,
                'gender' => $gender,
                'student_type_code' => (string) ($row->student_type_code ?: ''),
                'student_type' => $studentTypeLabel,
                'course_id' => $row->course_id ? (int) $row->course_id : null,
            ];
        })->values();

        return response()->json([
            'ok' => true,
            'data' => $payload,
            'total' => $payload->count(),
        ]);
    }

    public function studentDisciplineStudentSearch(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('q', ''));
        $limit = max(1, min(20, (int) $request->query('limit', 12)));

        $query = DB::table('students as s')
            ->leftJoin('courses as c', 'c.id', '=', 's.course_id')
            ->leftJoin('student_profiles as sp', 'sp.student_id', '=', 's.id')
            ->select(
                's.id',
                's.student_no',
                's.name as student_name',
                's.sex as student_sex',
                's.course_id',
                'c.code as course_code',
                'c.name as course_name',
                'sp.date_of_birth',
                'sp.gender as profile_gender'
            )
            ->whereNotNull('s.student_no')
            ->where('s.student_no', '<>', '')
            ->whereNotNull('s.name')
            ->where('s.name', '<>', '')
            ->when($search !== '', function ($builder) use ($search) {
                $builder->where(function ($inner) use ($search) {
                    $like = '%' . $search . '%';

                    $inner->where('s.student_no', 'like', $like)
                        ->orWhere('s.name', 'like', $like)
                        ->orWhere('c.code', 'like', $like)
                        ->orWhere('c.name', 'like', $like);
                });
            });

        if (Schema::hasTable('student_discipline_students')) {
            $query->leftJoin('student_discipline_students as sds', 'sds.student_id', '=', 's.id')
                ->addSelect('sds.id as discipline_student_id');
        } else {
            $query->addSelect(DB::raw('NULL as discipline_student_id'));
        }

        $rows = $query
            ->orderBy('s.student_no')
            ->limit($limit)
            ->get();

        $payload = $rows->map(function ($row) {
            $courseLabel = trim((string) ($row->course_name ?: $row->course_code));

            $gender = $this->disciplineNormalizeGenderLabel($row->profile_gender ?: $row->student_sex);

            $birthDate = '-';
            $birthDateIso = null;
            if (!empty($row->date_of_birth)) {
                $birthDateIso = Carbon::parse($row->date_of_birth)->format('Y-m-d');
                $birthDate = Carbon::parse($row->date_of_birth)->format('m/d/Y');
            }

            return [
                'id' => (int) $row->id,
                'student_no' => (string) $row->student_no,
                'name' => (string) $row->student_name,
                'course_id' => $row->course_id ? (int) $row->course_id : null,
                'course_label' => $courseLabel !== '' ? $courseLabel : '-',
                'birth_date' => $birthDate,
                'birth_date_iso' => $birthDateIso,
                'gender' => $gender,
                'discipline_student_id' => $row->discipline_student_id ? (int) $row->discipline_student_id : null,
            ];
        })->values();

        return response()->json([
            'ok' => true,
            'data' => $payload,
        ]);
    }

    public function studentDisciplineProgramSearch(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('q', ''));
        $limit = max(1, min(20, (int) $request->query('limit', 12)));

        $rows = Course::query()
            ->select('id', 'code', 'name')
            ->when($search !== '', function ($builder) use ($search) {
                $builder->where(function ($inner) use ($search) {
                    $like = '%' . $search . '%';

                    $inner->where('code', 'like', $like)
                        ->orWhere('name', 'like', $like);
                });
            })
            ->orderBy('code')
            ->limit($limit)
            ->get();

        $payload = $rows->map(function ($row) {
            $label = trim((string) ($row->code . ' - ' . $row->name));

            return [
                'id' => (int) $row->id,
                'code' => (string) $row->code,
                'name' => (string) $row->name,
                'label' => $label,
            ];
        })->values();

        return response()->json([
            'ok' => true,
            'data' => $payload,
        ]);
    }

    public function studentDisciplineStudentStore(Request $request): JsonResponse
    {
        if (!$this->disciplineTablesReady()) {
            return response()->json([
                'message' => 'Student Discipline tables are not ready. Please run migrations first.',
            ], 409);
        }

        $validated = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'course_id' => 'nullable|integer|exists:courses,id',
            'student_type_code' => 'required|string|max:40',
            'gender' => 'required|string|in:Male,Female',
            'birth_date' => 'nullable|date',
        ]);

        $studentType = StudentDisciplineStudentType::query()
            ->where('code', strtoupper(trim((string) $validated['student_type_code'])))
            ->first();

        if (!$studentType) {
            return response()->json([
                'message' => 'Selected student type is invalid.',
                'errors' => [
                    'student_type_code' => ['Please choose a valid student type.'],
                ],
            ], 422);
        }

        $disciplineStudent = StudentDisciplineStudent::query()
            ->firstOrNew(['student_id' => (int) $validated['student_id']]);

        $disciplineStudent->course_id = isset($validated['course_id']) ? (int) $validated['course_id'] : null;
        $disciplineStudent->student_type_id = (int) $studentType->id;
        $disciplineStudent->save();

        $this->disciplineSyncStudentDemographics(
            (int) $validated['student_id'],
            (string) $validated['gender'],
            isset($validated['birth_date']) ? (string) $validated['birth_date'] : null
        );

        return response()->json([
            'ok' => true,
            'data' => $this->disciplineStudentPayload($disciplineStudent->id),
        ]);
    }

    public function studentDisciplineStudentUpdate(Request $request, StudentDisciplineStudent $studentDisciplineStudent): JsonResponse
    {
        if (!$this->disciplineTablesReady()) {
            return response()->json([
                'message' => 'Student Discipline tables are not ready. Please run migrations first.',
            ], 409);
        }

        $validated = $request->validate([
            'course_id' => 'nullable|integer|exists:courses,id',
            'student_type_code' => 'required|string|max:40',
            'gender' => 'required|string|in:Male,Female',
            'birth_date' => 'nullable|date',
        ]);

        $studentType = StudentDisciplineStudentType::query()
            ->where('code', strtoupper(trim((string) $validated['student_type_code'])))
            ->first();

        if (!$studentType) {
            return response()->json([
                'message' => 'Selected student type is invalid.',
                'errors' => [
                    'student_type_code' => ['Please choose a valid student type.'],
                ],
            ], 422);
        }

        $studentDisciplineStudent->course_id = isset($validated['course_id']) ? (int) $validated['course_id'] : null;
        $studentDisciplineStudent->student_type_id = (int) $studentType->id;
        $studentDisciplineStudent->save();

        $this->disciplineSyncStudentDemographics(
            (int) $studentDisciplineStudent->student_id,
            (string) $validated['gender'],
            isset($validated['birth_date']) ? (string) $validated['birth_date'] : null
        );

        return response()->json([
            'ok' => true,
            'data' => $this->disciplineStudentPayload($studentDisciplineStudent->id),
        ]);
    }

    public function studentDisciplineStudentDestroy(StudentDisciplineStudent $studentDisciplineStudent): JsonResponse
    {
        if (!$this->disciplineTablesReady()) {
            return response()->json([
                'message' => 'Student Discipline tables are not ready. Please run migrations first.',
            ], 409);
        }

        $studentDisciplineStudent->delete();

        return response()->json(['ok' => true]);
    }

    public function studentDisciplineRecords(StudentDisciplineStudent $studentDisciplineStudent): JsonResponse
    {
        if (!$this->disciplineTablesReady()) {
            return response()->json([
                'ok' => true,
                'data' => [],
            ]);
        }

        $rows = DB::table('student_discipline_records as r')
            ->leftJoin('student_discipline_case_types as ct', 'ct.id', '=', 'r.case_type_id')
            ->leftJoin('student_discipline_action_types as at', 'at.id', '=', 'r.action_type_id')
            ->select(
                'r.id',
                'r.discipline_student_id',
                'r.case_type_id',
                'ct.label as case_type_label',
                'r.action_type_id',
                'at.label as action_type_label',
                'r.incident_date',
                'r.walk_in',
                'r.called_by',
                'r.description',
                'r.action_date',
                'r.counselor',
                'r.remarks',
                'r.is_completed',
                'r.updated_by'
            )
            ->where('r.discipline_student_id', $studentDisciplineStudent->id)
            ->orderByDesc('r.incident_date')
            ->orderByDesc('r.id')
            ->get();

        $payload = $rows->map(function ($row) {
            return [
                'id' => (int) $row->id,
                'discipline_student_id' => (int) $row->discipline_student_id,
                'case_type_id' => $row->case_type_id ? (int) $row->case_type_id : null,
                'case_type' => (string) ($row->case_type_label ?: '-'),
                'action_type_id' => $row->action_type_id ? (int) $row->action_type_id : null,
                'action_type' => (string) ($row->action_type_label ?: '-'),
                'incident_date' => $row->incident_date ? (string) $row->incident_date : null,
                'walk_in' => (bool) $row->walk_in,
                'called_by' => (string) ($row->called_by ?: ''),
                'description' => (string) ($row->description ?: ''),
                'action_date' => $row->action_date ? (string) $row->action_date : null,
                'counselor' => (string) ($row->counselor ?: ''),
                'remarks' => (string) ($row->remarks ?: ''),
                'is_completed' => (bool) $row->is_completed,
                'updated_by' => (string) ($row->updated_by ?: ''),
            ];
        })->values();

        return response()->json([
            'ok' => true,
            'data' => $payload,
        ]);
    }

    public function studentDisciplineRecordStore(Request $request, StudentDisciplineStudent $studentDisciplineStudent): JsonResponse
    {
        if (!$this->disciplineTablesReady()) {
            return response()->json([
                'message' => 'Student Discipline tables are not ready. Please run migrations first.',
            ], 409);
        }

        $validated = $request->validate([
            'incident_date' => 'required|date',
            'case_type_id' => 'required|integer|exists:student_discipline_case_types,id',
            'walk_in' => 'nullable|boolean',
            'called_by' => 'nullable|string|max:120',
            'description' => 'nullable|string|max:1500',
            'action_date' => 'nullable|date',
            'action_type_id' => 'nullable|integer|exists:student_discipline_action_types,id',
            'counselor' => 'nullable|string|max:120',
            'remarks' => 'nullable|string|max:1500',
            'is_completed' => 'nullable|boolean',
        ]);

        $sqlLikeErrors = $this->disciplineSqlInjectionErrors($validated, [
            'called_by',
            'description',
            'counselor',
            'remarks',
        ]);

        if (count($sqlLikeErrors)) {
            return response()->json([
                'message' => 'Input contains disallowed SQL-like patterns.',
                'errors' => $sqlLikeErrors,
            ], 422);
        }

        $record = StudentDisciplineRecord::query()->create([
            'discipline_student_id' => $studentDisciplineStudent->id,
            'incident_date' => $validated['incident_date'],
            'case_type_id' => (int) $validated['case_type_id'],
            'walk_in' => (bool) ($validated['walk_in'] ?? false),
            'called_by' => isset($validated['called_by']) ? trim((string) $validated['called_by']) : null,
            'description' => isset($validated['description']) ? trim((string) $validated['description']) : null,
            'action_date' => $validated['action_date'] ?? null,
            'action_type_id' => isset($validated['action_type_id']) ? (int) $validated['action_type_id'] : null,
            'counselor' => isset($validated['counselor']) ? trim((string) $validated['counselor']) : null,
            'remarks' => isset($validated['remarks']) ? trim((string) $validated['remarks']) : null,
            'is_completed' => (bool) ($validated['is_completed'] ?? false),
            'updated_by' => optional($request->user())->name ?: 'Registrar',
        ]);

        return response()->json([
            'ok' => true,
            'data' => $this->disciplineRecordPayload($record->id),
        ]);
    }

    public function studentDisciplineRecordUpdate(Request $request, StudentDisciplineRecord $studentDisciplineRecord): JsonResponse
    {
        if (!$this->disciplineTablesReady()) {
            return response()->json([
                'message' => 'Student Discipline tables are not ready. Please run migrations first.',
            ], 409);
        }

        $validated = $request->validate([
            'incident_date' => 'required|date',
            'case_type_id' => 'required|integer|exists:student_discipline_case_types,id',
            'walk_in' => 'nullable|boolean',
            'called_by' => 'nullable|string|max:120',
            'description' => 'nullable|string|max:1500',
            'action_date' => 'nullable|date',
            'action_type_id' => 'nullable|integer|exists:student_discipline_action_types,id',
            'counselor' => 'nullable|string|max:120',
            'remarks' => 'nullable|string|max:1500',
            'is_completed' => 'nullable|boolean',
        ]);

        $sqlLikeErrors = $this->disciplineSqlInjectionErrors($validated, [
            'called_by',
            'description',
            'counselor',
            'remarks',
        ]);

        if (count($sqlLikeErrors)) {
            return response()->json([
                'message' => 'Input contains disallowed SQL-like patterns.',
                'errors' => $sqlLikeErrors,
            ], 422);
        }

        $studentDisciplineRecord->update([
            'incident_date' => $validated['incident_date'],
            'case_type_id' => (int) $validated['case_type_id'],
            'walk_in' => (bool) ($validated['walk_in'] ?? false),
            'called_by' => isset($validated['called_by']) ? trim((string) $validated['called_by']) : null,
            'description' => isset($validated['description']) ? trim((string) $validated['description']) : null,
            'action_date' => $validated['action_date'] ?? null,
            'action_type_id' => isset($validated['action_type_id']) ? (int) $validated['action_type_id'] : null,
            'counselor' => isset($validated['counselor']) ? trim((string) $validated['counselor']) : null,
            'remarks' => isset($validated['remarks']) ? trim((string) $validated['remarks']) : null,
            'is_completed' => (bool) ($validated['is_completed'] ?? false),
            'updated_by' => optional($request->user())->name ?: 'Registrar',
        ]);

        return response()->json([
            'ok' => true,
            'data' => $this->disciplineRecordPayload($studentDisciplineRecord->id),
        ]);
    }

    public function studentDisciplineRecordDestroy(StudentDisciplineRecord $studentDisciplineRecord): JsonResponse
    {
        if (!$this->disciplineTablesReady()) {
            return response()->json([
                'message' => 'Student Discipline tables are not ready. Please run migrations first.',
            ], 409);
        }

        $studentDisciplineRecord->delete();

        return response()->json(['ok' => true]);
    }

    public function family(Request $request)
    {
        $search = trim((string) $request->get('q', ''));
        $parent = trim((string) $request->get('parent_name', ''));
        $yearLevel = trim((string) $request->get('year_level', ''));
        $withSiblings = (bool) $request->get('with_siblings', false);

        $familyRows = StudentProfile::query()
            ->leftJoin('students', 'student_profiles.student_no', '=', 'students.student_no')
            ->leftJoin('year_blocks', 'students.year_block_id', '=', 'year_blocks.id')
            ->select(
                'student_profiles.id',
                'student_profiles.student_no',
                'student_profiles.first_name',
                'student_profiles.middle_name',
                'student_profiles.last_name',
                'student_profiles.number_of_siblings',
                'student_profiles.first_in_family_college',
                'student_profiles.profile_complete',
                'student_profiles.mother_firstname',
                'student_profiles.mother_lastname',
                'student_profiles.father_firstname',
                'student_profiles.father_lastname',
                'student_profiles.guardian_firstname',
                'student_profiles.guardian_lastname',
                'year_blocks.label as year_level'
            )
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('student_profiles.student_no', 'like', '%' . $search . '%')
                        ->orWhere('student_profiles.first_name', 'like', '%' . $search . '%')
                        ->orWhere('student_profiles.last_name', 'like', '%' . $search . '%');
                });
            })
            ->when($parent !== '', function ($query) use ($parent) {
                $query->where(function ($inner) use ($parent) {
                    $inner->where('student_profiles.mother_firstname', 'like', '%' . $parent . '%')
                        ->orWhere('student_profiles.mother_lastname', 'like', '%' . $parent . '%')
                        ->orWhere('student_profiles.father_firstname', 'like', '%' . $parent . '%')
                        ->orWhere('student_profiles.father_lastname', 'like', '%' . $parent . '%')
                        ->orWhere('student_profiles.guardian_firstname', 'like', '%' . $parent . '%')
                        ->orWhere('student_profiles.guardian_lastname', 'like', '%' . $parent . '%');
                });
            })
            ->when($yearLevel !== '', function ($query) use ($yearLevel) {
                $query->where('year_blocks.label', $yearLevel);
            })
            ->when($withSiblings, function ($query) {
                $query->where('student_profiles.number_of_siblings', '>', 0);
            })
            ->orderBy('student_profiles.last_name')
            ->orderBy('student_profiles.first_name')
            ->paginate(10)
            ->appends($request->except('page'));

        foreach ($familyRows->items() as $row) {
            $row->display_name = trim(implode(', ', array_filter(array(
                trim((string) $row->last_name),
                trim(implode(' ', array_filter(array(
                    trim((string) $row->first_name),
                    trim((string) $row->middle_name),
                )))),
            ))));

            if ($row->display_name === '') {
                $row->display_name = 'N/A';
            }

            $familyKey = trim(implode('|', array_filter(array(
                trim((string) $row->mother_lastname),
                trim((string) $row->mother_firstname),
                trim((string) $row->father_lastname),
                trim((string) $row->father_firstname),
                trim((string) $row->guardian_lastname),
                trim((string) $row->guardian_firstname),
            ))));

            if ($familyKey === '') {
                $familyKey = (string) $row->student_no;
            }

            $row->family_code = 'FAM-' . strtoupper(substr(md5($familyKey), 0, 8));
            $row->eldest_label = (int) $row->first_in_family_college === 1 ? 'Yes' : 'No';
            $row->status_label = (int) $row->profile_complete === 1 ? 'Active' : 'Inactive';
        }

        return view('registrar.services.student-account.family', compact('familyRows', 'search', 'parent', 'yearLevel', 'withSiblings'));
    }

    public function changePassword()
    {
        return view('registrar.services.student-account.change-password');
    }

    private function disciplineTablesReady()
    {
        return Schema::hasTable('student_discipline_students')
            && Schema::hasTable('student_discipline_student_types')
            && Schema::hasTable('student_discipline_case_types')
            && Schema::hasTable('student_discipline_action_types')
            && Schema::hasTable('student_discipline_records');
    }

    private function disciplineStudentTypeOptions()
    {
        $rows = [];

        if (Schema::hasTable('student_discipline_student_types')) {
            $rows = StudentDisciplineStudentType::query()
                ->orderBy('label')
                ->get(['code', 'label'])
                ->map(function ($row) {
                    return [
                        'value' => (string) $row->code,
                        'label' => (string) $row->label,
                    ];
                })
                ->values()
                ->all();
        }

        if (!count($rows)) {
            $rows = [
                ['value' => 'OLD', 'label' => 'Old'],
                ['value' => 'NEW', 'label' => 'New'],
                ['value' => 'TRANSFEREE', 'label' => 'Transferee'],
            ];
        }

        return $rows;
    }

    private function disciplineCaseTypeOptions()
    {
        $rows = [];

        if (Schema::hasTable('student_discipline_case_types')) {
            $rows = StudentDisciplineCaseType::query()
                ->orderBy('label')
                ->get(['id', 'label'])
                ->map(function ($row) {
                    return [
                        'value' => (string) $row->id,
                        'label' => (string) $row->label,
                    ];
                })
                ->values()
                ->all();
        }

        if (!count($rows)) {
            $rows = [
                ['value' => '', 'label' => '-select type-'],
            ];
        }

        return $rows;
    }

    private function disciplineActionTypeOptions()
    {
        $rows = [];

        if (Schema::hasTable('student_discipline_action_types')) {
            $rows = StudentDisciplineActionType::query()
                ->orderBy('label')
                ->get(['id', 'label'])
                ->map(function ($row) {
                    return [
                        'value' => (string) $row->id,
                        'label' => (string) $row->label,
                    ];
                })
                ->values()
                ->all();
        }

        if (!count($rows)) {
            $rows = [
                ['value' => '', 'label' => '-select type-'],
            ];
        }

        return $rows;
    }

    private function disciplineTermAliases($term)
    {
        $value = strtolower(trim((string) $term));

        if ($value === 'first' || $value === 'first semester' || $value === '1st semester') {
            return ['First Semester', '1st Semester', 'First'];
        }

        if ($value === 'second' || $value === 'second semester' || $value === '2nd semester') {
            return ['Second Semester', '2nd Semester', 'Second'];
        }

        if ($value === 'summer' || $value === 'summer semester') {
            return ['Summer', 'Summer Semester'];
        }

        return [];
    }

    private function disciplineStudentPayload($disciplineStudentId)
    {
        $row = DB::table('student_discipline_students as sds')
            ->join('students as s', 's.id', '=', 'sds.student_id')
            ->leftJoin('courses as c', 'c.id', '=', 'sds.course_id')
            ->leftJoin('student_profiles as sp', 'sp.student_id', '=', 's.id')
            ->leftJoin('student_discipline_student_types as st', 'st.id', '=', 'sds.student_type_id')
            ->select(
                'sds.id as discipline_student_id',
                's.id as student_id',
                's.student_no',
                's.name as student_name',
                's.sex as student_sex',
                'sds.course_id',
                'c.code as course_code',
                'c.name as course_name',
                'sp.date_of_birth',
                'sp.gender as profile_gender',
                'st.code as student_type_code',
                'st.label as student_type_label'
            )
            ->where('sds.id', $disciplineStudentId)
            ->first();

        if (!$row) {
            return null;
        }

        $degreeProgram = trim((string) ($row->course_name ?: $row->course_code));
        if ($degreeProgram === '') {
            $degreeProgram = '-';
        }

        $birthDate = '-';
        $birthDateIso = null;
        if (!empty($row->date_of_birth)) {
            $birthDateIso = Carbon::parse($row->date_of_birth)->format('Y-m-d');
            $birthDate = Carbon::parse($row->date_of_birth)->format('m/d/Y');
        }

        $gender = $this->disciplineNormalizeGenderLabel($row->profile_gender ?: $row->student_sex);

        return [
            'discipline_student_id' => (int) $row->discipline_student_id,
            'student_id' => (int) $row->student_id,
            'student_no' => (string) $row->student_no,
            'name' => (string) $row->student_name,
            'degree_program' => $degreeProgram,
            'birth_date' => $birthDate,
            'birth_date_iso' => $birthDateIso,
            'gender' => $gender,
            'student_type_code' => (string) ($row->student_type_code ?: ''),
            'student_type' => (string) ($row->student_type_label ?: '-'),
            'course_id' => $row->course_id ? (int) $row->course_id : null,
        ];
    }

    private function disciplineSyncStudentDemographics($studentId, $gender, $birthDate)
    {
        $normalizedGender = $this->disciplineNormalizeGenderLabel($gender);

        DB::table('students')
            ->where('id', (int) $studentId)
            ->update([
                'sex' => $normalizedGender,
                'updated_at' => now(),
            ]);

        if (!Schema::hasTable('student_profiles')) {
            return;
        }

        $student = DB::table('students')
            ->select('id', 'student_no')
            ->where('id', (int) $studentId)
            ->first();

        if (!$student) {
            return;
        }

        $profile = StudentProfile::query()
            ->where('student_id', (int) $studentId)
            ->first();

        if (!$profile && trim((string) $student->student_no) !== '') {
            $profile = StudentProfile::query()
                ->where('student_no', (string) $student->student_no)
                ->first();
        }

        if (!$profile) {
            $profile = new StudentProfile();
        }

        $profile->student_id = (int) $studentId;

        if (trim((string) $profile->student_no) === '' && trim((string) $student->student_no) !== '') {
            $profile->student_no = (string) $student->student_no;
        }

        $profile->gender = $normalizedGender;
        $profile->date_of_birth = $birthDate !== null && trim((string) $birthDate) !== ''
            ? $birthDate
            : null;
        $profile->save();
    }

    private function disciplineNormalizeGenderLabel($value)
    {
        $normalized = strtolower(trim((string) $value));
        if ($normalized === 'female' || $normalized === 'f') {
            return 'Female';
        }

        if ($normalized === 'male' || $normalized === 'm') {
            return 'Male';
        }

        return 'Male';
    }

    private function disciplineRecordPayload($recordId)
    {
        $row = DB::table('student_discipline_records as r')
            ->leftJoin('student_discipline_case_types as ct', 'ct.id', '=', 'r.case_type_id')
            ->leftJoin('student_discipline_action_types as at', 'at.id', '=', 'r.action_type_id')
            ->select(
                'r.id',
                'r.discipline_student_id',
                'r.case_type_id',
                'ct.label as case_type_label',
                'r.action_type_id',
                'at.label as action_type_label',
                'r.incident_date',
                'r.walk_in',
                'r.called_by',
                'r.description',
                'r.action_date',
                'r.counselor',
                'r.remarks',
                'r.is_completed',
                'r.updated_by'
            )
            ->where('r.id', $recordId)
            ->first();

        if (!$row) {
            return null;
        }

        return [
            'id' => (int) $row->id,
            'discipline_student_id' => (int) $row->discipline_student_id,
            'case_type_id' => $row->case_type_id ? (int) $row->case_type_id : null,
            'case_type' => (string) ($row->case_type_label ?: '-'),
            'action_type_id' => $row->action_type_id ? (int) $row->action_type_id : null,
            'action_type' => (string) ($row->action_type_label ?: '-'),
            'incident_date' => $row->incident_date ? (string) $row->incident_date : null,
            'walk_in' => (bool) $row->walk_in,
            'called_by' => (string) ($row->called_by ?: ''),
            'description' => (string) ($row->description ?: ''),
            'action_date' => $row->action_date ? (string) $row->action_date : null,
            'counselor' => (string) ($row->counselor ?: ''),
            'remarks' => (string) ($row->remarks ?: ''),
            'is_completed' => (bool) $row->is_completed,
            'updated_by' => (string) ($row->updated_by ?: ''),
        ];
    }

    private function disciplineSqlInjectionErrors(array $payload, array $fields)
    {
        $patterns = [
            '/\\bunion\\s+select\\b/i',
            '/\\bor\\s+1\\s*=\\s*1\\b/i',
            "/\\b(and|or)\\b\\s+['\\\"]?[0-9a-z_]+['\\\"]?\\s*=\\s*['\\\"]?[0-9a-z_]+['\\\"]?/i",
            '/;\\s*(drop|truncate|alter|insert|update|delete|create)\\b/i',
            '/--|#|\\/\\*/',
            '/\\b(information_schema|sleep\\s*\\(|benchmark\\s*\\()\\b/i',
        ];

        $errors = [];

        foreach ($fields as $field) {
            $value = trim((string) data_get($payload, $field, ''));
            if ($value === '') {
                continue;
            }

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $value)) {
                    $errors[$field] = ['Please remove SQL-like operators or keywords from this field.'];
                    break;
                }
            }
        }

        return $errors;
    }
}
