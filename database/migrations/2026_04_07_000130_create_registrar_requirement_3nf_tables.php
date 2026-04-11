<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateRegistrarRequirement3nfTables extends Migration
{
    public function up()
    {
        Schema::create('registrar_requirement_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code', 40)->unique();
            $table->string('name', 100);
            $table->timestamps();
        });

        Schema::create('registrar_requirement_definitions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('requirement_name', 180);
            $table->unsignedBigInteger('registrar_requirement_type_id');
            $table->boolean('non_filipino_only')->default(false);
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->timestamps();

            $table->foreign('registrar_requirement_type_id', 'rr_definitions_type_fk')
                ->references('id')
                ->on('registrar_requirement_types')
                ->onDelete('restrict');
            $table->foreign('created_by_user_id', 'rr_definitions_creator_fk')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->index('requirement_name', 'rr_definitions_name_idx');
            $table->unique(
                ['requirement_name', 'registrar_requirement_type_id', 'non_filipino_only'],
                'rr_definitions_unique'
            );
        });

        Schema::create('registrar_requirement_policies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('registrar_requirement_definition_id');
            $table->unsignedBigInteger('system_school_semester_id');
            $table->unsignedBigInteger('year_block_id')->nullable();
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->timestamps();

            $table->foreign('registrar_requirement_definition_id', 'rr_policies_definition_fk')
                ->references('id')
                ->on('registrar_requirement_definitions')
                ->onDelete('cascade');
            $table->foreign('system_school_semester_id', 'rr_policies_semester_fk')
                ->references('id')
                ->on('system_school_semesters')
                ->onDelete('cascade');
            $table->foreign('year_block_id', 'rr_policies_year_block_fk')
                ->references('id')
                ->on('year_blocks')
                ->onDelete('set null');
            $table->foreign('created_by_user_id', 'rr_policies_creator_fk')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->unique(
                ['registrar_requirement_definition_id', 'system_school_semester_id', 'year_block_id'],
                'rr_policies_unique'
            );
        });

        if (!Schema::hasColumn('registrar_requirements', 'registrar_requirement_policy_id')) {
            Schema::table('registrar_requirements', function (Blueprint $table) {
                $table->unsignedBigInteger('registrar_requirement_policy_id')->nullable()->after('id');
                $table->index('registrar_requirement_policy_id', 'registrar_requirements_policy_idx');
                $table->foreign('registrar_requirement_policy_id', 'registrar_requirements_policy_fk')
                    ->references('id')
                    ->on('registrar_requirement_policies')
                    ->onDelete('set null');
            });
        }

        Schema::create('student_requirement_statuses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('registrar_requirement_policy_id');
            $table->boolean('is_submitted')->default(false);
            $table->string('remarks', 500)->nullable();
            $table->date('date_verified')->nullable();
            $table->unsignedBigInteger('verified_by_user_id')->nullable();
            $table->timestamps();

            $table->foreign('student_id', 'srs_student_fk')
                ->references('id')
                ->on('students')
                ->onDelete('cascade');
            $table->foreign('registrar_requirement_policy_id', 'srs_policy_fk')
                ->references('id')
                ->on('registrar_requirement_policies')
                ->onDelete('cascade');
            $table->foreign('verified_by_user_id', 'srs_verified_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->unique(['student_id', 'registrar_requirement_policy_id'], 'srs_unique_student_policy');
            $table->index('is_submitted', 'srs_submitted_idx');
            $table->index('date_verified', 'srs_verified_date_idx');
        });

        $this->seedRequirementTypes();
        $this->backfillLegacyRequirements();
    }

    public function down()
    {
        if (Schema::hasColumn('registrar_requirements', 'registrar_requirement_policy_id')) {
            Schema::table('registrar_requirements', function (Blueprint $table) {
                $table->dropForeign('registrar_requirements_policy_fk');
                $table->dropIndex('registrar_requirements_policy_idx');
                $table->dropColumn('registrar_requirement_policy_id');
            });
        }

        Schema::dropIfExists('student_requirement_statuses');
        Schema::dropIfExists('registrar_requirement_policies');
        Schema::dropIfExists('registrar_requirement_definitions');
        Schema::dropIfExists('registrar_requirement_types');
    }

    private function seedRequirementTypes()
    {
        $now = Carbon::now();

        $rows = [
            ['code' => 'DOCUMENT', 'name' => 'Document', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'MEDICAL', 'name' => 'Medical', 'created_at' => $now, 'updated_at' => $now],
        ];

        foreach ($rows as $row) {
            $exists = DB::table('registrar_requirement_types')
                ->where('code', $row['code'])
                ->exists();

            if (!$exists) {
                DB::table('registrar_requirement_types')->insert($row);
            }
        }
    }

    private function backfillLegacyRequirements()
    {
        $legacyRows = DB::table('registrar_requirements')
            ->orderBy('id')
            ->get();

        if ($legacyRows->isEmpty()) {
            return;
        }

        $activeSemesterId = $this->resolveActiveSemesterId();
        $now = Carbon::now();

        foreach ($legacyRows as $legacyRow) {
            $typeCode = $this->normalizeRequirementTypeCode($legacyRow->requirement_type);

            $typeId = DB::table('registrar_requirement_types')
                ->where('code', $typeCode)
                ->value('id');

            if (empty($typeId)) {
                $typeId = DB::table('registrar_requirement_types')->insertGetId([
                    'code' => $typeCode,
                    'name' => $this->resolveTypeName($typeCode),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $definitionId = DB::table('registrar_requirement_definitions')
                ->where('requirement_name', $legacyRow->requirement_name)
                ->where('registrar_requirement_type_id', $typeId)
                ->where('non_filipino_only', (int) $legacyRow->non_filipino)
                ->value('id');

            if (empty($definitionId)) {
                $definitionId = DB::table('registrar_requirement_definitions')->insertGetId([
                    'requirement_name' => $legacyRow->requirement_name,
                    'registrar_requirement_type_id' => $typeId,
                    'non_filipino_only' => (int) $legacyRow->non_filipino,
                    'created_by_user_id' => $legacyRow->created_by_user_id,
                    'created_at' => $legacyRow->created_at ?: $now,
                    'updated_at' => $legacyRow->updated_at ?: $now,
                ]);
            }

            $policyQuery = DB::table('registrar_requirement_policies')
                ->where('registrar_requirement_definition_id', $definitionId)
                ->where('system_school_semester_id', $activeSemesterId);

            $isAllYearLevel = (bool) $legacyRow->applies_to_all_year_levels || empty($legacyRow->year_block_id);

            if ($isAllYearLevel) {
                $policyQuery->whereNull('year_block_id');
            } else {
                $policyQuery->where('year_block_id', $legacyRow->year_block_id);
            }

            $policyId = $policyQuery->value('id');

            if (empty($policyId)) {
                $policyId = DB::table('registrar_requirement_policies')->insertGetId([
                    'registrar_requirement_definition_id' => $definitionId,
                    'system_school_semester_id' => $activeSemesterId,
                    'year_block_id' => $isAllYearLevel ? null : $legacyRow->year_block_id,
                    'created_by_user_id' => $legacyRow->created_by_user_id,
                    'created_at' => $legacyRow->created_at ?: $now,
                    'updated_at' => $legacyRow->updated_at ?: $now,
                ]);
            }

            DB::table('registrar_requirements')
                ->where('id', $legacyRow->id)
                ->update([
                    'registrar_requirement_policy_id' => $policyId,
                    'updated_at' => $now,
                ]);
        }
    }

    private function resolveActiveSemesterId()
    {
        $latestSemesterId = DB::table('system_school_semesters')
            ->orderByDesc('id')
            ->value('id');

        if (!empty($latestSemesterId)) {
            return (int) $latestSemesterId;
        }

        $yearStart = (int) Carbon::now()->format('Y');
        $schoolYear = $yearStart . '-' . ($yearStart + 1);
        $now = Carbon::now();

        return (int) DB::table('system_school_semesters')->insertGetId([
            'school_year' => $schoolYear,
            'semester' => 'First Semester',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function normalizeRequirementTypeCode($value)
    {
        $normalized = strtoupper(trim((string) $value));

        if ($normalized === 'MEDICAL') {
            return 'MEDICAL';
        }

        return 'DOCUMENT';
    }

    private function resolveTypeName($code)
    {
        if ($code === 'MEDICAL') {
            return 'Medical';
        }

        return 'Document';
    }
}
