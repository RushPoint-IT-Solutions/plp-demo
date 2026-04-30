<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReligionsTable extends Migration
{
    public function up()
    {
        Schema::create('religions', function (Blueprint $table) {
            $table->increments('id');

            $table->string('name')->unique();

            $table->unsignedInteger('created_by')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('religions');
    }
}