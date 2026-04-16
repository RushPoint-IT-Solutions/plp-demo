<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserAccount3nfSeeder extends Seeder
{
    public function run()
    {
        if (!Schema::hasTable('users')
            || !Schema::hasTable('user_account_types')
            || !Schema::hasTable('user_account_states')
            || !Schema::hasTable('user_account_profiles')) {
            return;
        }

        $now = now();

        $typeRows = [
            ['code' => 'student', 'name' => 'Student'],
            ['code' => 'applicant', 'name' => 'Applicant'],
            ['code' => 'faculty', 'name' => 'Faculty'],
            ['code' => 'registrar', 'name' => 'Registrar'],
            ['code' => 'admin', 'name' => 'Admin'],
            ['code' => 'user', 'name' => 'User'],
        ];

        foreach ($typeRows as $row) {
            DB::table('user_account_types')->updateOrInsert(
                ['code' => $row['code']],
                [
                    'name' => $row['name'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        $stateRows = [
            ['code' => 'active', 'name' => 'Active'],
            ['code' => 'inactive', 'name' => 'Inactive'],
        ];

        foreach ($stateRows as $row) {
            DB::table('user_account_states')->updateOrInsert(
                ['code' => $row['code']],
                [
                    'name' => $row['name'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        $typeIdByCode = DB::table('user_account_types')->pluck('id', 'code')->all();
        $stateIdByCode = DB::table('user_account_states')->pluck('id', 'code')->all();

        if (!isset($stateIdByCode['active']) || !isset($stateIdByCode['inactive'])) {
            return;
        }

        $legacyInactiveByUserId = [];
        if (Schema::hasTable('user_account_statuses')) {
            $legacyInactiveByUserId = DB::table('user_account_statuses')
                ->pluck('is_inactive', 'user_id')
                ->all();
        }

        DB::table('users')
            ->orderBy('id')
            ->chunk(250, function ($rows) use ($now, $typeIdByCode, $stateIdByCode, $legacyInactiveByUserId) {
                foreach ($rows as $row) {
                    $moduleCode = $this->normalizeModuleCode(isset($row->module) ? $row->module : null);
                    if (!isset($typeIdByCode[$moduleCode])) {
                        $moduleCode = 'user';
                    }

                    $isInactive = !empty($legacyInactiveByUserId[$row->id]);
                    $stateId = $isInactive ? $stateIdByCode['inactive'] : $stateIdByCode['active'];

                    DB::table('user_account_profiles')->updateOrInsert(
                        ['user_id' => $row->id],
                        [
                            'user_account_type_id' => $typeIdByCode[$moduleCode],
                            'user_account_state_id' => $stateId,
                            'is_sample' => false,
                            'updated_at' => $now,
                            'created_at' => $now,
                        ]
                    );
                }
            });

        // Seed one sample credential account per main user type.
        $sampleRows = [
            [
                'module' => 'student',
                'type_code' => 'student',
                'username' => 'student.sample',
                'password' => 'student.sample',
                'name' => 'Student Sample User',
                'email' => 'student.sample@plp.local',
                'fk' => 'student_id',
                'fk_table' => 'students',
            ],
            [
                'module' => 'applicant',
                'type_code' => 'applicant',
                'username' => 'applicant.sample',
                'password' => 'applicant.sample',
                'name' => 'Applicant Sample User',
                'email' => 'applicant.sample@plp.local',
                'fk' => 'applicant_id',
                'fk_table' => 'applicants',
            ],
            [
                'module' => 'faculty',
                'type_code' => 'faculty',
                'username' => 'faculty.sample',
                'password' => 'faculty.sample',
                'name' => 'Faculty Sample User',
                'email' => 'faculty.sample@plp.local',
                'fk' => 'faculty_id',
                'fk_table' => 'faculties',
            ],
            [
                'module' => 'registrar',
                'type_code' => 'registrar',
                'username' => 'registrar.sample',
                'password' => 'registrar.sample',
                'name' => 'Registrar Sample User',
                'email' => 'registrar.sample@plp.local',
                'fk' => 'registrar_id',
                'fk_table' => 'registrars',
            ],
        ];

        foreach ($sampleRows as $sample) {
            $foreignId = null;
            if (Schema::hasTable($sample['fk_table'])) {
                $foreignId = DB::table($sample['fk_table'])->orderBy('id')->value('id');
            }

            $userPayload = [
                'name' => $sample['name'],
                'email' => $sample['email'],
                'password' => Hash::make($sample['password']),
                'module' => $sample['module'],
                'force_password_reset' => false,
                'student_id' => null,
                'faculty_id' => null,
                'registrar_id' => null,
                'applicant_id' => null,
                'updated_at' => $now,
                'created_at' => $now,
            ];

            $userPayload[$sample['fk']] = $foreignId;

            DB::table('users')->updateOrInsert(
                ['username' => $sample['username']],
                $userPayload
            );

            $userId = DB::table('users')->where('username', $sample['username'])->value('id');
            if (!$userId) {
                continue;
            }

            DB::table('user_account_profiles')->updateOrInsert(
                ['user_id' => $userId],
                [
                    'user_account_type_id' => $typeIdByCode[$sample['type_code']],
                    'user_account_state_id' => $stateIdByCode['active'],
                    'is_sample' => true,
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

    private function normalizeModuleCode($module)
    {
        $code = strtolower(trim((string) $module));
        if ($code === '') {
            return 'user';
        }

        if ($code === 'administrator') {
            return 'admin';
        }

        return $code;
    }
}
