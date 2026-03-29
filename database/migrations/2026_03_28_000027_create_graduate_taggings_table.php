<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGraduateTaggingsTable extends Migration
{
    public function up()
    {
        Schema::create('graduate_taggings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id')->unique();
            $table->boolean('is_graduate')->default(false);
            $table->date('date_graduated')->nullable();
            $table->string('so_number')->nullable();
            $table->date('so_date')->nullable();
            $table->boolean('suspend_account')->default(false);
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('graduate_taggings');
    }
}
