<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentScholasticCommentsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('student_scholastic_comments')) {
            return;
        }

        Schema::create('student_scholastic_comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id');
            $table->string('school_year', 20)->nullable();
            $table->string('semester', 40)->nullable();
            $table->text('comment')->nullable();
            $table->date('date_issued')->nullable();
            $table->string('issued_by')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'school_year', 'semester'], 'ssc_student_term_idx');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_scholastic_comments');
    }
}
