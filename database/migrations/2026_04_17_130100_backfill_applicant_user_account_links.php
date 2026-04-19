<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BackfillApplicantUserAccountLinks extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('users') || !Schema::hasTable('applicants')) {
            return;
        }

        $now = now();

        if (Schema::hasColumn('users', 'module')
            && Schema::hasColumn('users', 'applicant_id')
            && Schema::hasColumn('users', 'username')
            && Schema::hasColumn('applicants', 'applicant_id')) {
            $candidateUsers = DB::table('users')
                ->select('id', 'username')
                ->whereNull('applicant_id')
                ->whereRaw("LOWER(TRIM(COALESCE(module, ''))) = ?", ['applicant'])
                ->get();

            foreach ($candidateUsers as $candidateUser) {
                $username = trim((string) $candidateUser->username);
                if ($username === '') {
                    continue;
                }

                $applicantPk = DB::table('applicants')
                    ->where('applicant_id', $username)
                    ->value('id');

                if (!$applicantPk) {
                    continue;
                }

                DB::table('users')
                    ->where('id', (int) $candidateUser->id)
                    ->update([
                        'applicant_id' => (int) $applicantPk,
                        'updated_at' => $now,
                    ]);
            }

            DB::table('users')
                ->whereNotNull('applicant_id')
                ->where(function ($query) {
                    $query->whereNull('module')
                        ->orWhereRaw('LOWER(TRIM(module)) <> ?', ['applicant']);
                })
                ->update([
                    'module' => 'applicant',
                    'updated_at' => $now,
                ]);
        }

        if (!$this->hasNormalizedUserAccountTables()) {
            return;
        }

        DB::table('user_account_types')->updateOrInsert(
            ['code' => 'applicant'],
            [
                'name' => 'Applicant',
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

        DB::table('user_account_states')->updateOrInsert(
            ['code' => 'inactive'],
            [
                'name' => 'Inactive',
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        $typeId = DB::table('user_account_types')
            ->where('code', 'applicant')
            ->value('id');

        $activeStateId = DB::table('user_account_states')
            ->where('code', 'active')
            ->value('id');

        if (!$typeId || !$activeStateId) {
            return;
        }

        $targetUserIds = DB::table('users')
            ->whereRaw("LOWER(TRIM(COALESCE(module, ''))) = ?", ['applicant'])
            ->whereNotNull('applicant_id')
            ->pluck('id')
            ->all();

        foreach ($targetUserIds as $targetUserId) {
            $existingProfile = DB::table('user_account_profiles')
                ->where('user_id', (int) $targetUserId)
                ->first();

            $stateId = (int) $activeStateId;
            $isSample = 0;

            if ($existingProfile) {
                if (!empty($existingProfile->user_account_state_id)) {
                    $stateId = (int) $existingProfile->user_account_state_id;
                }

                $isSample = $this->toBooleanInt(isset($existingProfile->is_sample) ? $existingProfile->is_sample : 0);
            }

            DB::table('user_account_profiles')->updateOrInsert(
                ['user_id' => (int) $targetUserId],
                [
                    'user_account_type_id' => (int) $typeId,
                    'user_account_state_id' => $stateId,
                    'is_sample' => $isSample,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }

    public function down()
    {
        // Intentionally no-op to avoid removing active applicant account links.
    }

    private function hasNormalizedUserAccountTables()
    {
        return Schema::hasTable('user_account_profiles')
            && Schema::hasTable('user_account_types')
            && Schema::hasTable('user_account_states');
    }

    private function toBooleanInt($value)
    {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        if (is_numeric($value)) {
            return ((int) $value) === 1 ? 1 : 0;
        }

        $normalized = strtolower(trim((string) $value));
        return in_array($normalized, ['1', 'true', 'on', 'yes'], true) ? 1 : 0;
    }
}
