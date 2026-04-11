<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('rooms')) {
            Schema::create('rooms', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('room_hallway_id');
                $table->unsignedInteger('room_number');
                $table->unsignedTinyInteger('floor_number');
                $table->unsignedSmallInteger('capacity');
                $table->unsignedBigInteger('updated_by_user_id')->nullable();
                $table->timestamps();

                $table->foreign('room_hallway_id', 'rooms_hallway_fk')
                    ->references('id')
                    ->on('room_hallways')
                    ->onDelete('restrict');
                $table->foreign('updated_by_user_id', 'rooms_updated_by_fk')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');

                $table->unique(['room_hallway_id', 'floor_number', 'room_number'], 'rooms_location_unique');
                $table->index(['room_hallway_id', 'floor_number'], 'rooms_hallway_floor_idx');
                $table->index('updated_by_user_id', 'rooms_updated_by_idx');
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
        Schema::dropIfExists('rooms');
    }
}
