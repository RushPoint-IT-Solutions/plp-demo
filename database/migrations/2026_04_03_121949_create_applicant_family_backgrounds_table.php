<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApplicantFamilyBackgroundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('applicant_family_backgrounds', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('applicant_id')->unique();

            $table->string('mother_last_name')->nullable();
            $table->string('mother_first_name')->nullable();
            $table->string('mother_middle_name')->nullable();
            $table->string('mother_nationality')->nullable();
            $table->string('mother_religion')->nullable();
            $table->date('mother_date_of_birth')->nullable();
            $table->string('mother_mobile_number')->nullable();
            $table->string('mother_occupation')->nullable();
            $table->string('mother_company_address')->nullable();
            $table->string('mother_estimated_monthly_income')->nullable();
            $table->string('mother_residence_address')->nullable();
            $table->string('mother_email_address')->nullable();

            $table->string('father_last_name')->nullable();
            $table->string('father_first_name')->nullable();
            $table->string('father_middle_name')->nullable();
            $table->string('father_nationality')->nullable();
            $table->string('father_religion')->nullable();
            $table->date('father_date_of_birth')->nullable();
            $table->string('father_mobile_number')->nullable();
            $table->string('father_occupation')->nullable();
            $table->string('father_company_address')->nullable();
            $table->string('father_estimated_monthly_income')->nullable();
            $table->string('father_residence_address')->nullable();
            $table->string('father_email_address')->nullable();

            $table->foreign('applicant_id')->references('id')->on('applicants')->onDelete('cascade');
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
        Schema::dropIfExists('applicant_family_backgrounds');
    }
}
