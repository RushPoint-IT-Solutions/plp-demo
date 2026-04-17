<?php

use App\ParentAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class ParentAuthSeeder extends Seeder
{
    public function run()
    {
        if (app()->environment('production')) {
            throw new RuntimeException('Cannot seed parent accounts in production.');
        }

        if (!Schema::hasTable('parents') || !Schema::hasTable('users')) {
            return;
        }

        $now = now();

        $this->ensureLookupRows($now);

        $parent = ParentAccount::updateOrCreate(
            ['parent_no' => 'PARENT-0001'],
            [
                'first_name' => 'Martha',
                'last_name' => 'Santos',
                'middle_name' => null,
                'suffix' => null,
                'email' => 'parent@example.com',
                'mobile_number' => '09171234567',
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        $user = DB::table('users')->where('username', 'parent')->first();
        $userPayload = [
            'name' => $parent->full_name ?: 'Martha Santos',
            'email' => $parent->email,
            'password' => Hash::make('parent'),
            'module' => 'parent',
            'force_password_reset' => false,
            'student_id' => null,
            'faculty_id' => null,
            'registrar_id' => null,
            'applicant_id' => null,
            'parent_id' => $parent->id,
            'updated_at' => $now,
            'created_at' => $now,
        ];

        DB::table('users')->updateOrInsert(
            ['username' => 'parent'],
            $userPayload
        );

        $userId = DB::table('users')->where('username', 'parent')->value('id');
        if ($userId && Schema::hasTable('user_account_profiles')) {
            $typeId = DB::table('user_account_types')->where('code', 'parent')->value('id');
            $stateId = DB::table('user_account_states')->where('code', 'active')->value('id');

            if ($typeId && $stateId) {
                DB::table('user_account_profiles')->updateOrInsert(
                    ['user_id' => $userId],
                    [
                        'user_account_type_id' => $typeId,
                        'user_account_state_id' => $stateId,
                        'is_sample' => true,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }
        }

        $primaryStudentId = null;
        $secondaryStudentId = null;

        if (Schema::hasTable('students')) {
            if (Schema::hasTable('student_subject_grades')) {
                $primaryStudentId = DB::table('student_subject_grades')
                    ->whereNotNull('student_id')
                    ->orderByDesc('id')
                    ->value('student_id');
            }

            if (!$primaryStudentId) {
                $primaryStudentId = DB::table('students')
                    ->where('student_no', '2025-000001')
                    ->value('id');
            }

            if (!$primaryStudentId) {
                $primaryStudentId = DB::table('students')->orderBy('id')->value('id');
            }

            if ($primaryStudentId) {
                $secondaryStudentId = DB::table('students')
                    ->where('id', '<>', $primaryStudentId)
                    ->orderBy('id')
                    ->value('id');
            }
        }

        if ($primaryStudentId && Schema::hasTable('parent_student_links')) {
            $relationshipTypeId = DB::table('parent_relationship_types')
                ->where('code', 'GUARDIAN')
                ->value('id');

            DB::table('parent_student_links')->updateOrInsert(
                [
                    'parent_id' => $parent->id,
                    'student_id' => $primaryStudentId,
                ],
                [
                    'relationship_type_id' => $relationshipTypeId,
                    'is_primary_contact' => true,
                    'receives_notifications' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            $this->seedStudentGradesIfMissing((int) $primaryStudentId, $now);

            if ($secondaryStudentId) {
                DB::table('parent_student_links')->updateOrInsert(
                    [
                        'parent_id' => $parent->id,
                        'student_id' => $secondaryStudentId,
                    ],
                    [
                        'relationship_type_id' => $relationshipTypeId,
                        'is_primary_contact' => false,
                        'receives_notifications' => true,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );

                $this->seedStudentGradesIfMissing((int) $secondaryStudentId, $now);
            }
        }
    }

    private function ensureLookupRows($now)
    {
        if (Schema::hasTable('parent_relationship_types')) {
            DB::table('parent_relationship_types')->updateOrInsert(
                ['code' => 'GUARDIAN'],
                [
                    'name' => 'Guardian',
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        if (Schema::hasTable('user_account_types')) {
            DB::table('user_account_types')->updateOrInsert(
                ['code' => 'parent'],
                [
                    'name' => 'Parent',
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        if (Schema::hasTable('user_account_states')) {
            DB::table('user_account_states')->updateOrInsert(
                ['code' => 'active'],
                [
                    'name' => 'Active',
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            DB::table('user_account_states')->updateOrInsert(
                ['code' => 'inactive'],
                [
                    'name' => 'Inactive',
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }

    private function seedStudentGradesIfMissing(int $studentId, $now)
    {
        if (!Schema::hasTable('student_subject_grades')
            || !Schema::hasTable('student_subject')
            || !Schema::hasTable('subjects')) {
            return;
        }

        $existingCount = DB::table('student_subject_grades')
            ->where('student_id', $studentId)
            ->count();

        if ($existingCount > 0) {
            return;
        }

        $subjectIds = DB::table('subjects')
            ->orderBy('id')
            ->limit(6)
            ->pluck('id')
            ->all();

        if (empty($subjectIds)) {
            return;
        }

        $gradeSet = [
            ['prelim' => 1.75, 'midterm' => 1.50, 'final' => 1.50, 'final_average' => 1.58, 'remarks' => 'Passed'],
            ['prelim' => 2.00, 'midterm' => 1.75, 'final' => 1.75, 'final_average' => 1.83, 'remarks' => 'Passed'],
            ['prelim' => 2.25, 'midterm' => 2.00, 'final' => 2.00, 'final_average' => 2.08, 'remarks' => 'Passed'],
        ];

        foreach ($subjectIds as $index => $subjectId) {
            DB::table('student_subject')->updateOrInsert(
                [
                    'student_id' => $studentId,
                    'subject_id' => $subjectId,
                ],
                [
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            $grade = $gradeSet[$index % count($gradeSet)];

            DB::table('student_subject_grades')->updateOrInsert(
                [
                    'student_id' => $studentId,
                    'subject_id' => $subjectId,
                ],
                [
                    'prelim' => $grade['prelim'],
                    'midterm' => $grade['midterm'],
                    'final' => $grade['final'],
                    'final_average' => $grade['final_average'],
                    'remarks' => $grade['remarks'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}