<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceRecordsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('attendance_records')) {
            Schema::create('attendance_records', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedBigInteger('subject_id');
                $table->unsignedBigInteger('student_id');
                $table->date('attendance_date');
                $table->string('status', 20)->default('Present');
                $table->string('remarks', 190)->nullable();
                $table->unsignedInteger('recorded_by_user_id')->nullable();
                $table->timestamps();

                $table->unique(['subject_id', 'student_id', 'attendance_date'], 'attendance_records_unique');
                $table->index(['subject_id', 'attendance_date']);

                $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
                $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('attendance_records');
    }
}
