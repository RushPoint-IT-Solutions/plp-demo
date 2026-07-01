<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegistrarEvaluationFormsTable extends Migration
{
    public function up()
    {
        Schema::create('registrar_evaluation_forms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('status', 20)->default('Draft');
            $table->unsignedInteger('responses')->default(0);
            $table->string('academic_year')->nullable();
            $table->string('program')->nullable();
            $table->string('subject_code')->nullable();
            $table->string('subject_name')->nullable();
            $table->string('faculty_name')->nullable();
            $table->date('period_from')->nullable();
            $table->date('period_to')->nullable();
            $table->string('target_respondents')->nullable();
            $table->text('blocks')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'updated_at']);
            $table->index('subject_code');
        });
    }

    public function down()
    {
        Schema::dropIfExists('registrar_evaluation_forms');
    }
}
