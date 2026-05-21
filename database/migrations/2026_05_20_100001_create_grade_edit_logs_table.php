<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGradeEditLogsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('grade_edit_logs')) {
            return;
        }

        Schema::create('grade_edit_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('subject_id');
            $table->string('field', 30);           // midterm | final | final_average | remarks
            $table->string('old_value', 200)->nullable();
            $table->string('new_value', 200)->nullable();
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('edited_by');
            $table->string('edited_by_name', 200)->nullable();
            $table->timestamp('edited_at')->useCurrent();
            $table->index(['student_id', 'subject_id'], 'gel_student_subject_idx');
            $table->index('edited_at', 'gel_edited_at_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('grade_edit_logs');
    }
}
