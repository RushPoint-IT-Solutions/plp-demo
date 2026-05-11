<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudentClinicRecordsTable extends Migration
{
    public function up()
    {
        Schema::create('student_clinic_records', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('student_id')->index();
            $table->date('visit_date');
            $table->string('chief_complaint')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('treatment')->nullable();
            $table->text('medications_given')->nullable();
            $table->decimal('temperature', 4, 1)->nullable();     // °C
            $table->string('blood_pressure', 20)->nullable();     // e.g. 120/80
            $table->unsignedSmallInteger('pulse_rate')->nullable(); // bpm
            $table->unsignedSmallInteger('respiratory_rate')->nullable(); // breaths/min
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->string('disposition', 100)->nullable();        // Sent home / Referred / etc.
            $table->string('referred_to')->nullable();
            $table->string('attended_by')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_clinic_records');
    }
}
