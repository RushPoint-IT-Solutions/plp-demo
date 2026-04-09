<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRoomFileSortIndexesToRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('rooms')) {
            return;
        }

        Schema::table('rooms', function (Blueprint $table) {
            $table->index(['floor_number', 'room_number'], 'rooms_floor_room_idx');
            $table->index('capacity', 'rooms_capacity_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('rooms')) {
            return;
        }

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropIndex('rooms_floor_room_idx');
            $table->dropIndex('rooms_capacity_idx');
        });
    }
}
