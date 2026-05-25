<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddSampleStudentDocumentUploadRequirement extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('student_requirement_statuses')) {
            return;
        }

        Schema::table('student_requirement_statuses', function (Blueprint $table) {
            if (!Schema::hasColumn('student_requirement_statuses', 'uploaded_original_name')) {
                $table->string('uploaded_original_name', 190)->nullable()->after('remarks');
            }
            if (!Schema::hasColumn('student_requirement_statuses', 'uploaded_path')) {
                $table->string('uploaded_path', 255)->nullable()->after('uploaded_original_name');
            }
            if (!Schema::hasColumn('student_requirement_statuses', 'uploaded_mime')) {
                $table->string('uploaded_mime', 120)->nullable()->after('uploaded_path');
            }
            if (!Schema::hasColumn('student_requirement_statuses', 'uploaded_size')) {
                $table->unsignedBigInteger('uploaded_size')->nullable()->after('uploaded_mime');
            }
            if (!Schema::hasColumn('student_requirement_statuses', 'uploaded_at')) {
                $table->timestamp('uploaded_at')->nullable()->after('uploaded_size');
            }
        });

        if (!Schema::hasTable('registrar_requirement_types')
            || !Schema::hasTable('registrar_requirement_definitions')
            || !Schema::hasTable('registrar_requirement_policies')
            || !Schema::hasTable('students')) {
            return;
        }

        $now = Carbon::now();
        $typeId = DB::table('registrar_requirement_types')->where('code', 'DOCUMENT')->value('id');
        if (!$typeId) {
            $typeId = DB::table('registrar_requirement_types')->insertGetId([
                'code' => 'DOCUMENT',
                'name' => 'Document',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $definitionId = DB::table('registrar_requirement_definitions')
            ->where('requirement_name', 'Sample Document Upload')
            ->where('registrar_requirement_type_id', $typeId)
            ->where('non_filipino_only', 0)
            ->value('id');

        if (!$definitionId) {
            $definitionId = DB::table('registrar_requirement_definitions')->insertGetId([
                'requirement_name' => 'Sample Document Upload',
                'registrar_requirement_type_id' => $typeId,
                'non_filipino_only' => 0,
                'created_by_user_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $semesterId = DB::table('system_school_semesters')->orderByDesc('id')->value('id');
        if (!$semesterId && Schema::hasTable('system_school_semesters')) {
            $year = (int) $now->format('Y');
            $semesterId = DB::table('system_school_semesters')->insertGetId([
                'school_year' => $year . '-' . ($year + 1),
                'semester' => 'First Semester',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if (!$semesterId) {
            return;
        }

        $policyId = DB::table('registrar_requirement_policies')
            ->where('registrar_requirement_definition_id', $definitionId)
            ->where('system_school_semester_id', $semesterId)
            ->whereNull('year_block_id')
            ->value('id');

        if (!$policyId) {
            $policyId = DB::table('registrar_requirement_policies')->insertGetId([
                'registrar_requirement_definition_id' => $definitionId,
                'system_school_semester_id' => $semesterId,
                'year_block_id' => null,
                'created_by_user_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('students')
            ->select('id')
            ->orderBy('id')
            ->chunkById(500, function ($students) use ($policyId, $now) {
                foreach ($students as $student) {
                    $exists = DB::table('student_requirement_statuses')
                        ->where('student_id', (int) $student->id)
                        ->where('registrar_requirement_policy_id', (int) $policyId)
                        ->exists();

                    if (!$exists) {
                        DB::table('student_requirement_statuses')->insert([
                            'student_id' => (int) $student->id,
                            'registrar_requirement_policy_id' => (int) $policyId,
                            'is_submitted' => false,
                            'remarks' => 'Sample requirement for document upload testing.',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }
            });
    }

    public function down()
    {
        if (Schema::hasTable('registrar_requirement_definitions')
            && Schema::hasTable('registrar_requirement_policies')
            && Schema::hasTable('student_requirement_statuses')) {
            $definitionIds = DB::table('registrar_requirement_definitions')
                ->where('requirement_name', 'Sample Document Upload')
                ->pluck('id');

            $policyIds = DB::table('registrar_requirement_policies')
                ->whereIn('registrar_requirement_definition_id', $definitionIds)
                ->pluck('id');

            DB::table('student_requirement_statuses')
                ->whereIn('registrar_requirement_policy_id', $policyIds)
                ->delete();

            DB::table('registrar_requirement_policies')
                ->whereIn('id', $policyIds)
                ->delete();

            DB::table('registrar_requirement_definitions')
                ->whereIn('id', $definitionIds)
                ->delete();
        }

        if (Schema::hasTable('student_requirement_statuses')) {
            Schema::table('student_requirement_statuses', function (Blueprint $table) {
                foreach (['uploaded_at', 'uploaded_size', 'uploaded_mime', 'uploaded_path', 'uploaded_original_name'] as $column) {
                    if (Schema::hasColumn('student_requirement_statuses', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
}
