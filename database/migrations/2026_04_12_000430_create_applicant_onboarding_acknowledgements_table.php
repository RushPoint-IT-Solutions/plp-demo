<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicantOnboardingAcknowledgementsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('applicant_onboarding_acknowledgements')) {
            return;
        }

        Schema::create('applicant_onboarding_acknowledgements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('applicant_id')->unique();
            $table->boolean('ack_notices')->default(false);
            $table->boolean('ack_terms')->default(false);
            $table->dateTime('acknowledged_at');
            $table->timestamps();

            $table->foreign('applicant_id')
                ->references('id')
                ->on('applicants')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        if (!Schema::hasTable('applicant_onboarding_acknowledgements')) {
            return;
        }

        Schema::dropIfExists('applicant_onboarding_acknowledgements');
    }
}
