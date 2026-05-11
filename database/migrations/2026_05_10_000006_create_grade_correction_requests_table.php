<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGradeCorrectionRequestsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('grade_correction_requests')) {
            return;
        }

        Schema::create('grade_correction_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id')->nullable();
            $table->string('student_no', 80)->nullable();
            $table->unsignedBigInteger('student_grade_record_id')->nullable();
            $table->string('action', 20);
            $table->string('status', 20)->default('pending');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('reviewer_remarks')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status'], 'gcr_student_status_idx');
            $table->index(['student_grade_record_id', 'status'], 'gcr_record_status_idx');
            $table->index(['action', 'status'], 'gcr_action_status_idx');

            if (Schema::hasTable('students')) {
                $table->foreign('student_id', 'gcr_student_fk')
                    ->references('id')
                    ->on('students')
                    ->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('grade_correction_requests');
    }
}
