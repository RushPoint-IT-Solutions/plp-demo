<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentUpdateRunsTable extends Migration
{
    public function up()
    {
        Schema::create('student_update_runs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('action_name', 80);
            $table->string('run_mode', 40)->nullable();
            $table->string('school_year', 30)->nullable();
            $table->string('term', 30)->nullable();
            $table->string('period', 80)->nullable();
            $table->string('operator', 120)->nullable();
            $table->string('course', 120)->nullable();
            $table->string('year_level', 30)->nullable();
            $table->string('section', 20)->nullable();
            $table->string('student_no', 80)->nullable();
            $table->boolean('include_unpaid_only')->default(false);
            $table->boolean('active_only')->default(false);
            $table->unsignedInteger('affected_count')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_update_runs');
    }
}
