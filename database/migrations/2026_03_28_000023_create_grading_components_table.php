<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGradingComponentsTable extends Migration
{
    public function up()
    {
        Schema::create('grading_components', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('school_year', 20);
            $table->string('period', 30);
            $table->string('semester', 20);
            $table->string('section', 40);
            $table->string('course_code', 40);
            $table->string('title', 120);
            $table->unsignedInteger('sequence_no');
            $table->decimal('percentage', 5, 2);
            $table->string('lab_mode', 20)->nullable();
            $table->string('cap', 20)->nullable();
            $table->string('updated_by', 80)->nullable();
            $table->date('effective_date')->nullable();
            $table->timestamps();

            $table->index(['school_year', 'semester', 'period']);
            $table->index(['section']);
            $table->index(['course_code']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('grading_components');
    }
}
