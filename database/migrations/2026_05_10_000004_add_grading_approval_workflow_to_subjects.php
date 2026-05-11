<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddGradingApprovalWorkflowToSubjects extends Migration
{
    public function up()
    {
        if (Schema::hasTable('subject_grading_statuses')) {
            $now = now();
            $statuses = [
                ['code' => 'Open For Encoding', 'label' => 'Open For Encoding'],
                ['code' => 'SUBMITTED', 'label' => 'Submitted for Dean Review'],
                ['code' => 'DEAN_APPROVED', 'label' => 'Dean Approved'],
                ['code' => 'REGISTRAR_FINALIZED', 'label' => 'Registrar Finalized'],
                ['code' => 'REJECTED', 'label' => 'Returned for Revision'],
            ];

            foreach ($statuses as $status) {
                DB::table('subject_grading_statuses')->updateOrInsert(
                    ['code' => $status['code']],
                    [
                        'label' => $status['label'],
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }
        }

        if (!Schema::hasTable('subjects')) {
            return;
        }

        Schema::table('subjects', function (Blueprint $table) {
            if (!Schema::hasColumn('subjects', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('grading_status_id');
            }
            if (!Schema::hasColumn('subjects', 'dean_approved_by')) {
                $table->unsignedBigInteger('dean_approved_by')->nullable()->after('submitted_at');
            }
            if (!Schema::hasColumn('subjects', 'dean_approved_at')) {
                $table->timestamp('dean_approved_at')->nullable()->after('dean_approved_by');
            }
            if (!Schema::hasColumn('subjects', 'registrar_finalized_by')) {
                $table->unsignedBigInteger('registrar_finalized_by')->nullable()->after('dean_approved_at');
            }
            if (!Schema::hasColumn('subjects', 'registrar_finalized_at')) {
                $table->timestamp('registrar_finalized_at')->nullable()->after('registrar_finalized_by');
            }
            if (!Schema::hasColumn('subjects', 'grading_returned_by')) {
                $table->unsignedBigInteger('grading_returned_by')->nullable()->after('registrar_finalized_at');
            }
            if (!Schema::hasColumn('subjects', 'grading_returned_at')) {
                $table->timestamp('grading_returned_at')->nullable()->after('grading_returned_by');
            }
            if (!Schema::hasColumn('subjects', 'grading_return_reason')) {
                $table->text('grading_return_reason')->nullable()->after('grading_returned_at');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('subjects')) {
            return;
        }

        Schema::table('subjects', function (Blueprint $table) {
            foreach ([
                'submitted_at',
                'dean_approved_by',
                'dean_approved_at',
                'registrar_finalized_by',
                'registrar_finalized_at',
                'grading_returned_by',
                'grading_returned_at',
                'grading_return_reason',
            ] as $column) {
                if (Schema::hasColumn('subjects', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
