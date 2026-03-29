<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentDeficienciesTable extends Migration
{
    public function up()
    {
        Schema::create('student_deficiencies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id');
            $table->string('department', 80);
            $table->string('remarks', 255);
            $table->date('date_today')->nullable();
            $table->date('submission_date')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->date('compliance_date')->nullable();
            $table->string('updated_by', 80)->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->index(['student_id', 'is_completed']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_deficiencies');
    }
}
