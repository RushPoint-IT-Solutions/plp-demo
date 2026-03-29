<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGradeRulesTable extends Migration
{
    public function up()
    {
        Schema::create('grade_rules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code', 20)->unique();
            $table->string('grade', 20)->nullable();
            $table->string('remarks', 255);
            $table->json('periods')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('grade_rules');
    }
}
