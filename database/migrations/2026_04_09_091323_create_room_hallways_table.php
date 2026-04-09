<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoomHallwaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('room_hallways')) {
            Schema::create('room_hallways', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('room_building_id');
                $table->string('name', 120);
                $table->timestamps();

                $table->foreign('room_building_id', 'room_hallways_building_fk')
                    ->references('id')
                    ->on('room_buildings')
                    ->onDelete('cascade');
                $table->unique(['room_building_id', 'name'], 'room_hallways_building_name_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('room_hallways');
    }
}
