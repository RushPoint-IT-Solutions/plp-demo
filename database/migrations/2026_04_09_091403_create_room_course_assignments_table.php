<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoomCourseAssignmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('room_course_assignments')) {
            Schema::create('room_course_assignments', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('room_id');
                $table->unsignedBigInteger('course_id');
                $table->unsignedBigInteger('assigned_by_user_id')->nullable();
                $table->timestamps();

                $table->foreign('room_id', 'room_course_assignments_room_fk')
                    ->references('id')
                    ->on('rooms')
                    ->onDelete('cascade');
                $table->foreign('course_id', 'room_course_assignments_course_fk')
                    ->references('id')
                    ->on('courses')
                    ->onDelete('restrict');
                $table->foreign('assigned_by_user_id', 'room_course_assignments_assigned_by_fk')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');

                $table->unique(['room_id', 'course_id'], 'room_course_assignments_unique');
                $table->index('course_id', 'room_course_assignments_course_idx');
                $table->index('assigned_by_user_id', 'room_course_assignments_assigned_by_idx');
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
        Schema::dropIfExists('room_course_assignments');
    }
}
