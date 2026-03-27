<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGradingPeriodsTable extends Migration
{
    public function up()
    {
        Schema::create('grading_periods', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('school_year', 20)->nullable();
            $table->string('semester', 20)->nullable();
            $table->string('section_subject_faculty', 150);
            $table->string('period', 30);
            $table->string('description', 120);
            $table->decimal('percentage', 5, 2);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->string('grading_computation', 50);
            $table->boolean('use_grades_library')->default(false);
            $table->timestamps();

            $table->index(['school_year', 'semester']);
            $table->index(['period']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('grading_periods');
    }
}
