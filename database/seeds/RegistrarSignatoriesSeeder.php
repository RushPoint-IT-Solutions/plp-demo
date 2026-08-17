<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Seeds the TOR/COG signatories (University Registrar, Assistant University
 * Registrar, and the five program-group Academic Secretaries) plus a login
 * account for each. The two university-level registrars are left unscoped
 * (full access to all courses/programs); each Academic Secretary is
 * course-scoped so they only see students in their assigned program(s).
 */
class RegistrarSignatoriesSeeder extends Seeder
{
    public function run()
    {
        if (!Schema::hasTable('system_config_signature_designations') || !Schema::hasTable('system_config_name_signatures')) {
            return;
        }

        $now = now();

        // Existing designations (from the 2026_04_16 migration) — just set the signer name.
        $this->upsertSigner('REGISTRAR', 'University Registrar', 10, 'MR. FEDERICO G. NUEVA', $now);
        $this->upsertSigner('ASSISTANT_REGISTRAR', 'Assistant University Registrar', 30, 'MS. JAY ANNE I. SANTOS', $now);

        $signatoryAccounts = [
            // University-level registrars: no course_codes means unrestricted
            // (full access to all courses/programs), same as the admin/registrar demo accounts.
            [
                'code' => 'REGISTRAR',
                'name' => 'University Registrar',
                'sort_order' => 10,
                'signer' => 'MR. FEDERICO G. NUEVA',
                'username' => 'fnueva',
                'course_codes' => [],
            ],
            [
                'code' => 'ASSISTANT_REGISTRAR',
                'name' => 'Assistant University Registrar',
                'sort_order' => 30,
                'signer' => 'MS. JAY ANNE I. SANTOS',
                'username' => 'jsantos',
                'course_codes' => [],
            ],
            [
                'code' => 'ACAD_SEC_HEALTH_HOSP',
                'name' => 'Academic Secretary - BSHM, BSN, BSMCS, BSHRM, AHRM, MAN',
                'sort_order' => 40,
                'signer' => 'MS. JULIE RUTH C. MALABANAN',
                'username' => 'jmalabanan',
                'course_codes' => ['BSHM', 'BSN', 'BSMCS', 'BSHRM', 'AHRM', 'MAN'],
            ],
            [
                'code' => 'ACAD_SEC_BUSINESS',
                'name' => 'Academic Secretary - BSBA',
                'sort_order' => 50,
                'signer' => 'MS. ANNLYN A. BENITO',
                'username' => 'abenito',
                'course_codes' => ['BSBA'],
            ],
            [
                'code' => 'ACAD_SEC_EDUCATION',
                'name' => 'Academic Secretary - BEED, BSED, CTP, MAEd',
                'sort_order' => 60,
                'signer' => 'MS. AIVEE B. DE LA CRUZ',
                'username' => 'adelacruz',
                'course_codes' => ['BEED', 'BEED-ECED', 'BEED-PRESCHOOL', 'BSED-BIO', 'BSED-COMPED', 'BSED-ENG', 'BSED-FIL', 'BSED-MATH', 'CTP', 'MAED-ADMIN', 'MAED-LEAD'],
            ],
            [
                'code' => 'ACAD_SEC_ARTS_ACCT_ENG',
                'name' => 'Academic Secretary - AB PSYCH, BSA, BSENT, BSECE',
                'sort_order' => 70,
                'signer' => 'MS. ELAFLOR F. SILAYAN',
                'username' => 'esilayan',
                'course_codes' => ['AB PSYCH', 'BSA', 'BSENT', 'BSECE'],
            ],
            [
                'code' => 'ACAD_SEC_COMPUTING',
                'name' => 'Academic Secretary - BSIT, BSCS, ACT',
                'sort_order' => 80,
                'signer' => 'MS. MARILYN D. GARCIA',
                'username' => 'mgarcia',
                'course_codes' => ['BSIT', 'BSCS', 'ACT'],
            ],
        ];

        $registrar = Schema::hasTable('registrars') ? DB::table('registrars')->where('code', 'REG-001')->first() : null;

        foreach ($signatoryAccounts as $row) {
            $this->upsertSigner($row['code'], $row['name'], $row['sort_order'], $row['signer'], $now);

            if (!Schema::hasTable('users') || !Schema::hasTable('user_course_scopes')) {
                continue;
            }

            DB::table('users')->updateOrInsert(
                ['username' => $row['username']],
                [
                    'name' => $this->titleCaseName($row['signer']),
                    'email' => $row['username'] . '@plp.local',
                    'password' => Hash::make($row['username']),
                    'module' => 'registrar',
                    'force_password_reset' => true,
                    'student_id' => null,
                    'faculty_id' => null,
                    'registrar_id' => $registrar ? $registrar->id : null,
                    'applicant_id' => null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            $userId = DB::table('users')->where('username', $row['username'])->value('id');
            if (!$userId) {
                continue;
            }

            $courseIds = DB::table('courses')->whereIn('code', $row['course_codes'])->pluck('id');
            foreach ($courseIds as $courseId) {
                DB::table('user_course_scopes')->updateOrInsert(
                    ['user_id' => $userId, 'course_id' => $courseId],
                    ['updated_at' => $now, 'created_at' => $now]
                );
            }
        }
    }

    private function upsertSigner(string $code, string $name, int $sortOrder, string $signerName, $now): void
    {
        DB::table('system_config_signature_designations')->updateOrInsert(
            ['code' => $code],
            ['name' => $name, 'sort_order' => $sortOrder, 'updated_at' => $now, 'created_at' => $now]
        );

        $designationId = DB::table('system_config_signature_designations')->where('code', $code)->value('id');

        DB::table('system_config_name_signatures')->updateOrInsert(
            ['designation_id' => $designationId],
            ['signer_name' => $signerName, 'is_active' => true, 'updated_at' => $now, 'created_at' => $now]
        );
    }

    private function titleCaseName(string $name): string
    {
        $name = preg_replace('/^(MR|MS|MRS|DR)\.\s*/i', '', trim($name));

        return ucwords(strtolower($name));
    }
}
