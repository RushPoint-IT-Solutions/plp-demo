<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateForm137aRequestsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('form_137a_requests')) {
            Schema::create('form_137a_requests', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('student_id');
                $table->unsignedTinyInteger('issuance_number');
                $table->timestamp('requested_at');
                $table->timestamp('printed_at')->nullable();
                $table->unsignedBigInteger('requested_by_user_id')->nullable();
                $table->unsignedBigInteger('printed_by_user_id')->nullable();
                $table->timestamps();

                $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
                $table->foreign('requested_by_user_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('printed_by_user_id')->references('id')->on('users')->onDelete('set null');
                $table->unique(['student_id', 'issuance_number'], 'form_137a_student_issuance_unique');
                $table->index(['student_id', 'printed_at'], 'form_137a_student_printed_index');
            });
        }

        $this->backfillExistingPrints();
    }

    public function down()
    {
        Schema::dropIfExists('form_137a_requests');
    }

    private function backfillExistingPrints(): void
    {
        if (!Schema::hasTable('audit_events') || !Schema::hasTable('audit_event_subjects')) {
            return;
        }

        $prints = DB::table('audit_events as ae')
            ->join('audit_event_subjects as aes', 'aes.audit_event_id', '=', 'ae.id')
            ->where('ae.event_code', 'F137A_PRINTED')
            ->where('aes.subject_type', 'Student')
            ->whereNotNull('aes.subject_id')
            ->orderBy('aes.subject_id')
            ->orderBy('ae.created_at')
            ->orderBy('ae.id')
            ->select('aes.subject_id as student_id', 'ae.actor_user_id', 'ae.created_at')
            ->get();

        $issuanceNumbers = [];
        foreach ($prints as $print) {
            $studentId = (int) $print->student_id;
            $issuanceNumber = ($issuanceNumbers[$studentId] ?? 0) + 1;
            $issuanceNumbers[$studentId] = $issuanceNumber;

            if ($issuanceNumber > 2 || DB::table('form_137a_requests')
                ->where('student_id', $studentId)
                ->where('issuance_number', $issuanceNumber)
                ->exists()) {
                continue;
            }

            DB::table('form_137a_requests')->insert([
                'student_id' => $studentId,
                'issuance_number' => $issuanceNumber,
                'requested_at' => $print->created_at,
                'printed_at' => $print->created_at,
                'requested_by_user_id' => $print->actor_user_id,
                'printed_by_user_id' => $print->actor_user_id,
                'created_at' => $print->created_at,
                'updated_at' => $print->created_at,
            ]);
        }
    }
}
