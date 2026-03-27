<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCancellationWaiversTable extends Migration
{
    public function up()
    {
        Schema::create('cancellation_waivers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id');
            $table->string('school_year')->nullable();
            $table->string('semester')->nullable();
            $table->string('program')->nullable();
            $table->string('year_level')->nullable();
            $table->string('section')->nullable();
            $table->string('status')->default('pending');
            $table->string('requested_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->string('remarks')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cancellation_waivers');
    }
}
