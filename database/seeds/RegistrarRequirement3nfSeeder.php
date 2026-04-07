<?php

use App\RegistrarRequirement;
use App\RegistrarRequirementDefinition;
use App\RegistrarRequirementPolicy;
use App\RegistrarRequirementType;
use App\Student;
use App\StudentRequirementStatus;
use App\SystemSchoolSemester;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RegistrarRequirement3nfSeeder extends Seeder
{
    public function run()
    {
        $this->seedRequirementTypes();

        $activeSemester = $this->resolveActiveSemester();

        $this->backfillPoliciesFromLegacyRows($activeSemester->id);
        $this->seedDefaultPolicies($activeSemester->id);
        $insertedStatuses = $this->seedStudentStatuses();

        $this->command->info(
            'RegistrarRequirement3nfSeeder complete: inserted ' . $insertedStatuses . ' student requirement status rows.'
        );
    }

    private function seedRequirementTypes()
    {
        $types = [
            ['code' => 'DOCUMENT', 'name' => 'Document'],
            ['code' => 'MEDICAL', 'name' => 'Medical'],
        ];

        foreach ($types as $typeData) {
            RegistrarRequirementType::query()->firstOrCreate(
                ['code' => $typeData['code']],
                ['name' => $typeData['name']]
            );
        }
    }

    private function resolveActiveSemester()
    {
        $semester = SystemSchoolSemester::query()
            ->orderByDesc('id')
            ->first();

        if ($semester) {
            return $semester;
        }

        $yearStart = (int) Carbon::now()->format('Y');

        return SystemSchoolSemester::query()->create([
            'school_year' => $yearStart . '-' . ($yearStart + 1),
            'semester' => 'First Semester',
        ]);
    }

    private function backfillPoliciesFromLegacyRows($activeSemesterId)
    {
        $legacyRows = RegistrarRequirement::query()
            ->orderBy('id')
            ->get();

        foreach ($legacyRows as $legacyRow) {
            $type = RegistrarRequirementType::query()->firstOrCreate(
                ['code' => $this->normalizeRequirementTypeCode($legacyRow->requirement_type)],
                ['name' => $legacyRow->requirement_type === 'Medical' ? 'Medical' : 'Document']
            );

            $definition = RegistrarRequirementDefinition::query()->firstOrCreate(
                [
                    'requirement_name' => $legacyRow->requirement_name,
                    'registrar_requirement_type_id' => $type->id,
                    'non_filipino_only' => (bool) $legacyRow->non_filipino,
                ],
                [
                    'created_by_user_id' => $legacyRow->created_by_user_id,
                ]
            );

            $policyQuery = RegistrarRequirementPolicy::query()
                ->where('registrar_requirement_definition_id', $definition->id)
                ->where('system_school_semester_id', $activeSemesterId);

            if ($legacyRow->applies_to_all_year_levels || empty($legacyRow->year_block_id)) {
                $policyQuery->whereNull('year_block_id');
            } else {
                $policyQuery->where('year_block_id', $legacyRow->year_block_id);
            }

            $policy = $policyQuery->first();

            if (!$policy) {
                $policy = RegistrarRequirementPolicy::query()->create([
                    'registrar_requirement_definition_id' => $definition->id,
                    'system_school_semester_id' => $activeSemesterId,
                    'year_block_id' => ($legacyRow->applies_to_all_year_levels || empty($legacyRow->year_block_id))
                        ? null
                        : $legacyRow->year_block_id,
                    'created_by_user_id' => $legacyRow->created_by_user_id,
                ]);
            }

            if ((int) $legacyRow->registrar_requirement_policy_id !== (int) $policy->id) {
                $legacyRow->registrar_requirement_policy_id = $policy->id;
                $legacyRow->save();
            }
        }
    }

    private function seedDefaultPolicies($activeSemesterId)
    {
        $defaultRows = [
            ['name' => 'Birth Certificate', 'type' => 'DOCUMENT', 'non_filipino_only' => false],
            ['name' => 'Form 138', 'type' => 'DOCUMENT', 'non_filipino_only' => false],
            ['name' => 'Certificate of Good Moral Character', 'type' => 'DOCUMENT', 'non_filipino_only' => false],
            ['name' => 'Medical Certificate', 'type' => 'MEDICAL', 'non_filipino_only' => false],
            ['name' => 'Chest X-Ray Result', 'type' => 'MEDICAL', 'non_filipino_only' => false],
            ['name' => 'Passport Copy', 'type' => 'DOCUMENT', 'non_filipino_only' => true],
        ];

        foreach ($defaultRows as $row) {
            $type = RegistrarRequirementType::query()->where('code', $row['type'])->first();

            if (!$type) {
                continue;
            }

            $definition = RegistrarRequirementDefinition::query()->firstOrCreate(
                [
                    'requirement_name' => $row['name'],
                    'registrar_requirement_type_id' => $type->id,
                    'non_filipino_only' => $row['non_filipino_only'],
                ]
            );

            RegistrarRequirementPolicy::query()->firstOrCreate([
                'registrar_requirement_definition_id' => $definition->id,
                'system_school_semester_id' => $activeSemesterId,
                'year_block_id' => null,
            ]);
        }
    }

    private function seedStudentStatuses()
    {
        $currentCount = StudentRequirementStatus::query()->count();

        if ($currentCount >= 100) {
            return 0;
        }

        $studentIds = Student::query()->orderBy('id')->pluck('id')->all();
        $policyIds = RegistrarRequirementPolicy::query()->orderBy('id')->pluck('id')->all();

        if (!count($studentIds) || !count($policyIds)) {
            return 0;
        }

        $targetAdditional = 100 - $currentCount;
        $inserted = 0;

        $studentCount = count($studentIds);
        $policyCount = count($policyIds);

        for ($i = 0; $i < $targetAdditional; $i++) {
            $studentId = $studentIds[$i % $studentCount];
            $policyId = $policyIds[(int) floor($i / $studentCount) % $policyCount];
            $isSubmitted = ($i % 3) !== 0;

            $status = StudentRequirementStatus::query()->firstOrNew([
                'student_id' => $studentId,
                'registrar_requirement_policy_id' => $policyId,
            ]);

            $wasExisting = $status->exists;

            $status->is_submitted = $isSubmitted;
            $status->remarks = $isSubmitted
                ? 'Auto-verified by RegistrarRequirement3nfSeeder #' . ($i + 1)
                : 'Pending student submission';
            $status->date_verified = $isSubmitted ? Carbon::today()->subDays($i % 20) : null;
            $status->verified_by_user_id = null;
            $status->save();

            if (!$wasExisting) {
                $inserted++;
            }
        }

        return $inserted;
    }

    private function normalizeRequirementTypeCode($type)
    {
        $normalized = strtoupper(trim((string) $type));

        if ($normalized === 'MEDICAL') {
            return 'MEDICAL';
        }

        return 'DOCUMENT';
    }
}
