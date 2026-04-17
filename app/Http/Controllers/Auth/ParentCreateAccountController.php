<?php

namespace App\Http\Controllers\Auth;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\NotificationDelivery;
use App\NotificationType;
use App\ParentAccount;
use App\ParentRelationshipType;
use App\ParentStudentLink;
use App\PortalNotification;
use App\Student;
use App\StudentProfile;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class ParentCreateAccountController extends Controller
{
    public function studentLookup(Request $request): JsonResponse
    {
        if (!Schema::hasTable('students')) {
            return response()->json([
                'ok' => true,
                'students' => [],
            ]);
        }

        $validated = $request->validate([
            'query' => 'nullable|string|max:80',
        ]);

        $query = trim((string) ($validated['query'] ?? ''));

        if ($query === '') {
            return response()->json([
                'ok' => true,
                'students' => [],
            ]);
        }

        $students = Student::query()
            ->select(['student_no', 'name'])
            ->where(function ($builder) use ($query) {
                $builder->where('student_no', 'like', '%' . $query . '%')
                    ->orWhere('name', 'like', '%' . $query . '%');
            })
            ->orderBy('student_no')
            ->limit(20)
            ->get()
            ->map(function ($student) {
                $studentNo = trim((string) $student->student_no);
                $studentName = trim((string) $student->name);

                return [
                    'student_no' => $studentNo,
                    'name' => $studentName,
                    'label' => trim($studentNo . ' - ' . $studentName, ' -'),
                ];
            })
            ->values();

        return response()->json([
            'ok' => true,
            'students' => $students,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (!$this->requiredTablesExist()) {
            return $this->redirectWithCreateErrors($request, [
                'parent_first_name' => 'Parent account tables are not ready. Please run migrations first.',
            ]);
        }

        $data = $this->validateWithBag('parentCreate', $request, [
            'parent_first_name' => 'required|string|max:120',
            'parent_middle_name' => 'nullable|string|max:120',
            'parent_last_name' => 'required|string|max:120',
            'parent_honorific' => 'required|string|in:MR,MRS,MS',
            'parent_email' => 'nullable|email|max:190|unique:parents,email|unique:users,email',
            'parent_relationship' => 'required|string|exists:parent_relationship_types,code',
            'child_student_no' => 'required|string|max:40',
            'child_birthdate' => 'required|date|before_or_equal:today',
            'parent_username' => 'required|string|min:4|max:191|alpha_dash|unique:users,username',
            'parent_password' => ['required', 'string', 'min:8', 'max:255', 'confirmed', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'],
        ], [
            'child_student_no.required' => 'Child student number is required.',
            'child_birthdate.required' => 'Child birthdate is required.',
            'parent_username.unique' => 'This parent already has an account. Please sign in instead.',
            'parent_email.unique' => 'This parent already has an account. Please sign in instead.',
            'parent_password.confirmed' => 'Password confirmation does not match.',
            'parent_password.regex' => 'Password must include at least one uppercase letter, one lowercase letter, and one number.',
            'parent_relationship.exists' => 'Please select a valid relationship type.',
        ]);

        $studentNo = trim((string) $data['child_student_no']);
        $student = Student::query()->where('student_no', $studentNo)->first();
        $studentProfile = $this->resolveStudentProfile($student);

        if (!$student) {
            return $this->redirectWithCreateErrors($request, [
                'child_student_no' => 'Student number was not found.',
            ]);
        }

        $birthdate = (string) $data['child_birthdate'];
        if (!$this->birthdateMatchesStudentRecord($studentProfile, $birthdate)) {
            return $this->redirectWithCreateErrors($request, [
                'child_birthdate' => 'Child birthdate does not match school records.',
            ]);
        }

        $relationshipType = ParentRelationshipType::query()
            ->where('code', strtoupper(trim((string) $data['parent_relationship'])))
            ->first();

        if (!$relationshipType) {
            return $this->redirectWithCreateErrors($request, [
                'parent_relationship' => 'Relationship type is not available.',
            ]);
        }

        $honorificId = null;
        if (Schema::hasTable('parent_honorifics') && Schema::hasColumn('parents', 'honorific_id')) {
            $honorificId = DB::table('parent_honorifics')
                ->where('code', strtoupper(trim((string) $data['parent_honorific'])))
                ->value('id');

            if (!$honorificId) {
                return $this->redirectWithCreateErrors($request, [
                    'parent_honorific' => 'Honorific lookup is not available. Please run migrations first.',
                ]);
            }
        }

        $createdUsername = null;

        DB::transaction(function () use ($data, $student, $studentProfile, $relationshipType, $honorificId, &$createdUsername) {
            $parentNo = $this->nextParentNumber();

            $parentPayload = [
                'parent_no' => $parentNo,
                'first_name' => trim((string) $data['parent_first_name']),
                'middle_name' => $this->nullableTrim($data['parent_middle_name'] ?? null),
                'last_name' => trim((string) $data['parent_last_name']),
                'suffix' => null,
                'email' => $this->nullableTrim($data['parent_email'] ?? null),
                'mobile_number' => null,
            ];

            if (!is_null($honorificId)) {
                $parentPayload['honorific_id'] = $honorificId;
            }

            $parent = ParentAccount::query()->create($parentPayload);

            ParentStudentLink::query()->create([
                'parent_id' => $parent->id,
                'student_id' => $student->id,
                'relationship_type_id' => $relationshipType->id,
                'is_primary_contact' => true,
                'receives_notifications' => true,
            ]);

            $fullName = trim(implode(' ', array_filter([
                $parent->first_name,
                $parent->middle_name,
                $parent->last_name,
            ])));

            $user = User::query()->create([
                'name' => $fullName,
                'username' => trim((string) $data['parent_username']),
                'email' => $this->nullableTrim($data['parent_email'] ?? null),
                'password' => Hash::make((string) $data['parent_password']),
                'module' => 'parent',
                'force_password_reset' => false,
                'student_id' => null,
                'faculty_id' => null,
                'registrar_id' => null,
                'applicant_id' => null,
                'parent_id' => $parent->id,
            ]);

            $this->syncParentUserAccountProfile((int) $user->id);

            $relationshipCode = strtoupper(trim((string) $relationshipType->code));
            if (in_array($relationshipCode, ['FATHER', 'MOTHER'], true)) {
                $studentSurname = $this->resolveStudentSurname($student, $studentProfile);
                if ($this->hasSurnameMismatch($parent->last_name, $studentSurname)) {
                    $this->createRegistrarSurnameMismatchNotification(
                        $parent,
                        $student,
                        $relationshipCode,
                        $studentSurname,
                        (int) $user->id
                    );
                }
            }

            $createdUsername = (string) $user->username;
        });

        return redirect()
            ->route('module.login', ['module' => 'parent'])
            ->with('parentCreateSuccess', 'Parent account created successfully. You can now sign in.')
            ->withInput(['username' => $createdUsername]);
    }

    private function requiredTablesExist(): bool
    {
        return Schema::hasTable('users')
            && Schema::hasTable('students')
            && Schema::hasTable('parents')
            && Schema::hasColumn('parents', 'honorific_id')
            && Schema::hasTable('parent_honorifics')
            && Schema::hasTable('parent_relationship_types')
            && Schema::hasTable('parent_student_links');
    }

    private function birthdateMatchesStudentRecord($profile, string $birthdate): bool
    {
        if (!$profile || empty($profile->date_of_birth)) {
            return true;
        }

        $profileBirthdate = $this->normalizeBirthdateValue($profile->date_of_birth);
        $submittedBirthdate = $this->normalizeBirthdateValue($birthdate);

        if ($profileBirthdate === null || $submittedBirthdate === null) {
            return false;
        }

        return $profileBirthdate === $submittedBirthdate;
    }

    private function normalizeBirthdateValue($value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->toDateString();
        }

        $normalizedValue = trim((string) $value);
        if ($normalizedValue === '') {
            return null;
        }

        try {
            return Carbon::parse($normalizedValue)->toDateString();
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private function resolveStudentProfile(Student $student = null)
    {
        if (!$student
            || !Schema::hasTable('student_profiles')
            || !Schema::hasColumn('student_profiles', 'date_of_birth')) {
            return null;
        }

        if (Schema::hasColumn('student_profiles', 'student_no')) {
            $profile = StudentProfile::query()
                ->where('student_no', $student->student_no)
                ->orderByDesc('id')
                ->first();

            if ($profile) {
                return $profile;
            }
        }

        return StudentProfile::query()
            ->where('student_id', $student->id)
            ->orderByDesc('id')
            ->first();
    }

    private function resolveStudentSurname(Student $student, $profile): string
    {
        $profileLastName = trim((string) data_get($profile, 'last_name'));
        if ($profileLastName !== '') {
            return $profileLastName;
        }

        $fullName = trim((string) $student->name);
        if ($fullName === '') {
            return '';
        }

        if (strpos($fullName, ',') !== false) {
            return trim((string) explode(',', $fullName)[0]);
        }

        $parts = preg_split('/\s+/', $fullName);
        if (!is_array($parts) || empty($parts)) {
            return $fullName;
        }

        return trim((string) end($parts));
    }

    private function hasSurnameMismatch(string $parentSurname, string $studentSurname): bool
    {
        $normalizedParent = $this->normalizeSurname($parentSurname);
        $normalizedStudent = $this->normalizeSurname($studentSurname);

        if ($normalizedParent === '' || $normalizedStudent === '') {
            return false;
        }

        return $normalizedParent !== $normalizedStudent;
    }

    private function normalizeSurname(string $surname): string
    {
        $uppercase = strtoupper(trim((string) $surname));
        $cleaned = preg_replace('/[^A-Z]/', '', $uppercase);

        if (!is_string($cleaned) || trim($cleaned) === '') {
            return $uppercase;
        }

        return $cleaned;
    }

    private function createRegistrarSurnameMismatchNotification(
        ParentAccount $parent,
        Student $student,
        string $relationshipCode,
        string $studentSurname,
        int $createdByUserId
    ): void {
        if (!Schema::hasTable('notification_types')
            || !Schema::hasTable('portal_notifications')
            || !Schema::hasTable('notification_deliveries')
            || !Schema::hasTable('users')) {
            return;
        }

        $registrarUserIds = User::query()
            ->where('module', 'registrar')
            ->pluck('id')
            ->all();

        if (empty($registrarUserIds)) {
            return;
        }

        $type = NotificationType::query()->firstOrCreate(
            ['code' => 'PARENT_SURNAME_MISMATCH_REVIEW'],
            ['name' => 'Parent Surname Mismatch Review']
        );

        $relationshipLabel = $relationshipCode === 'FATHER' ? 'Father' : 'Mother';
        $studentNo = trim((string) $student->student_no);
        $studentName = trim((string) $student->name);
        $parentSurname = trim((string) $parent->last_name);
        $sourceReference = 'parent-surname-mismatch:' . $parent->parent_no . ':' . $studentNo . ':' . strtolower($relationshipCode);

        $message = $relationshipLabel
            . ' registration uses surname "' . $parentSurname
            . '" while student ' . $studentNo . ' (' . $studentName
            . ') has surname "' . $studentSurname
            . '". Please validate supporting records.';

        $notification = PortalNotification::query()->firstOrCreate(
            [
                'source_module' => 'parent',
                'source_reference' => $sourceReference,
            ],
            [
                'notification_type_id' => $type->id,
                'title' => 'Parent-Student Surname Mismatch Needs Review',
                'message' => $message,
                'source_url' => '/registrar/dashboard',
                'created_by_user_id' => $createdByUserId ?: null,
            ]
        );

        if ((int) $notification->notification_type_id !== (int) $type->id || (string) $notification->message !== (string) $message) {
            $notification->notification_type_id = $type->id;
            $notification->message = $message;
            $notification->created_by_user_id = $createdByUserId ?: null;
            $notification->save();
        }

        $now = now();
        foreach ($registrarUserIds as $registrarUserId) {
            NotificationDelivery::query()->updateOrCreate(
                [
                    'portal_notification_id' => $notification->id,
                    'user_id' => (int) $registrarUserId,
                ],
                [
                    'delivered_at' => $now,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }

    private function nextParentNumber(): string
    {
        $maxNumber = (int) ParentAccount::query()
            ->lockForUpdate()
            ->where('parent_no', 'like', 'PARENT-%')
            ->selectRaw('MAX(CAST(SUBSTRING(parent_no, 8) AS UNSIGNED)) as max_parent_no')
            ->value('max_parent_no');

        $nextNumber = $maxNumber + 1;

        while (true) {
            $candidate = 'PARENT-' . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
            $exists = ParentAccount::query()
                ->lockForUpdate()
                ->where('parent_no', $candidate)
                ->exists();

            if (!$exists) {
                return $candidate;
            }

            $nextNumber++;
        }
    }

    private function nullableTrim($value)
    {
        $trimmed = trim((string) $value);
        return $trimmed === '' ? null : $trimmed;
    }

    private function redirectWithCreateErrors(Request $request, array $messages): RedirectResponse
    {
        return redirect()
            ->route('module.login', ['module' => 'parent'])
            ->withErrors($messages, 'parentCreate')
            ->withInput($request->except(['parent_password', 'parent_password_confirmation']));
    }

    private function syncParentUserAccountProfile(int $userId): void
    {
        if (!Schema::hasTable('user_account_types')
            || !Schema::hasTable('user_account_states')
            || !Schema::hasTable('user_account_profiles')) {
            return;
        }

        $now = now();

        DB::table('user_account_types')->updateOrInsert(
            ['code' => 'parent'],
            [
                'name' => 'Parent',
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        DB::table('user_account_states')->updateOrInsert(
            ['code' => 'active'],
            [
                'name' => 'Active',
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        $typeId = DB::table('user_account_types')->where('code', 'parent')->value('id');
        $stateId = DB::table('user_account_states')->where('code', 'active')->value('id');

        if (!$typeId || !$stateId) {
            return;
        }

        DB::table('user_account_profiles')->updateOrInsert(
            ['user_id' => $userId],
            [
                'user_account_type_id' => $typeId,
                'user_account_state_id' => $stateId,
                'is_sample' => false,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        if (Schema::hasTable('user_account_statuses')) {
            DB::table('user_account_statuses')->updateOrInsert(
                ['user_id' => $userId],
                [
                    'is_inactive' => false,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}
