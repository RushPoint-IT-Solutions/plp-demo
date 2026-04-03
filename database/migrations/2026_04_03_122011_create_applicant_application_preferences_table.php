<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApplicantApplicationPreferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('applicant_application_preferences', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('applicant_id')->unique();
            $table->string('apply_program')->nullable();
            $table->string('apply_strand')->nullable();
            $table->unsignedBigInteger('apply_course_id')->nullable();
            $table->string('entry_classification')->nullable();
            $table->string('year_level')->nullable();
            $table->string('semester')->nullable();
            $table->string('school_year')->nullable();
            $table->date('application_date')->nullable();
            $table->string('campus')->default('Pasig');

            $table->index('apply_program');
            $table->index('apply_course_id');
            $table->foreign('applicant_id')->references('id')->on('applicants')->onDelete('cascade');
            $table->foreign('apply_course_id')->references('id')->on('courses')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('applicant_application_preferences');
    }
}
