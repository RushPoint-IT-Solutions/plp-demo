<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudentMedicalRecordsTable extends Migration
{
    public function up()
    {
        Schema::create('student_medical_records', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('student_id')->index();
            $table->string('blood_type', 10)->nullable();
            $table->decimal('height_cm', 5, 2)->nullable();
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->text('allergies')->nullable();
            $table->text('medical_conditions')->nullable();
            $table->text('current_medications')->nullable();
            $table->text('past_illnesses')->nullable();
            $table->string('immunization_status', 30)->nullable(); // Complete / Incomplete / Unknown
            $table->text('immunization_notes')->nullable();
            $table->string('vision_od', 20)->nullable();  // right eye
            $table->string('vision_os', 20)->nullable();  // left eye
            $table->string('hearing_right', 20)->nullable();
            $table->string('hearing_left', 20)->nullable();
            $table->text('dental_status')->nullable();
            $table->text('mental_health_notes')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_number', 30)->nullable();
            $table->string('emergency_contact_relation', 50)->nullable();
            $table->date('last_physical_exam_date')->nullable();
            $table->text('exam_findings')->nullable();
            $table->string('physician_name')->nullable();
            $table->text('remarks')->nullable();
            $table->string('recorded_by')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_medical_records');
    }
}
