<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDiplomaSignatoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('diploma_signatories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('student_no');
            $table->string('copy_type', 20)->default('print-2');
            $table->string('registrar_name')->nullable();
            $table->string('registrar_title')->nullable();
            $table->string('president_name')->nullable();
            $table->string('president_title')->nullable();
            $table->string('chairman_name')->nullable();
            $table->string('chairman_title')->nullable();
            $table->timestamps();

            $table->unique(['student_no', 'copy_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('diploma_signatories');
    }
}
