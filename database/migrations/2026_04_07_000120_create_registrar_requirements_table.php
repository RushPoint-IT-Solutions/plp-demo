<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegistrarRequirementsTable extends Migration
{
    public function up()
    {
        Schema::create('registrar_requirements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('year_block_id')->nullable();
            $table->boolean('applies_to_all_year_levels')->default(false);
            $table->string('requirement_name');
            $table->enum('requirement_type', ['Medical', 'Document'])->default('Document');
            $table->boolean('non_filipino')->default(false);
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->timestamps();

            $table->foreign('year_block_id')->references('id')->on('year_blocks')->onDelete('set null');
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('set null');

            $table->index('requirement_name');
            $table->index('year_block_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('registrar_requirements');
    }
}
