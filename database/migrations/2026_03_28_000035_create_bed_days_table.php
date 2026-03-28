<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBedDaysTable extends Migration
{
    public function up()
    {
        Schema::create('bed_days', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('school_year', 30);
            $table->string('semester', 30);
            $table->string('month_name', 30);
            $table->unsignedTinyInteger('number_of_days');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bed_days');
    }
}
