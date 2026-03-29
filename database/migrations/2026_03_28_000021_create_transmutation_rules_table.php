<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransmutationRulesTable extends Migration
{
    public function up()
    {
        Schema::create('transmutation_rules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('school_year', 20);
            $table->string('term', 20);
            $table->string('program', 50);
            $table->decimal('initial_from', 6, 2);
            $table->decimal('initial_to', 6, 2);
            $table->decimal('transmuted_grade', 6, 2);
            $table->string('code', 10);
            $table->string('remarks', 100);
            $table->timestamps();

            $table->index(['school_year', 'term']);
            $table->index(['program']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('transmutation_rules');
    }
}
