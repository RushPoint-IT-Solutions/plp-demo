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

        $studentId = null;
        if (Schema::hasTable('students')) {
            $studentId = DB::table('students')->orderBy('id')->value('id');
        }

        if ($studentId && Schema::hasTable('parent_student_links')) {
            $relationshipTypeId = DB::table('parent_relationship_types')
                ->where('code', 'GUARDIAN')
                ->value('id');

            DB::table('parent_student_links')->updateOrInsert(
                [
                    'parent_id' => $parent->id,
                    'student_id' => $studentId,
                ],
                [
                    'relationship_type_id' => $relationshipTypeId,
                    'is_primary_contact' => true,
                    'receives_notifications' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
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
}