<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegistrarEvaluationResponsesTable extends Migration
{
    public function up()
    {
        Schema::create('registrar_evaluation_responses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('evaluation_form_id');
            $table->text('answers');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->foreign('evaluation_form_id')->references('id')->on('registrar_evaluation_forms')->onDelete('cascade');
            $table->index('evaluation_form_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('registrar_evaluation_responses');
    }
}
