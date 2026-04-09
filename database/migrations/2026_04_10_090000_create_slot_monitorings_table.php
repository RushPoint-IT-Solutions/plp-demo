<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSlotMonitoringsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('slot_monitorings')) {
            Schema::create('slot_monitorings', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('school_year', 20);
                $table->string('semester', 20);
                $table->unsignedBigInteger('course_id');
                $table->string('section', 120);
                $table->string('subject', 150);
                $table->string('schedule', 190);
                $table->unsignedSmallInteger('total_slots');
                $table->unsignedSmallInteger('enrolled_slots')->default(0);
                $table->unsignedBigInteger('updated_by_user_id')->nullable();
                $table->timestamps();

                $table->foreign('course_id', 'slot_monitorings_course_fk')
                    ->references('id')
                    ->on('courses')
                    ->onDelete('restrict');

                $table->foreign('updated_by_user_id', 'slot_monitorings_updated_by_fk')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');

                $table->unique([
                    'school_year',
                    'semester',
                    'course_id',
                    'section',
                    'subject',
                    'schedule',
                ], 'slot_monitorings_unique');

                $table->index(['school_year', 'semester', 'course_id'], 'slot_monitorings_filter_idx');
                $table->index('section', 'slot_monitorings_section_idx');
                $table->index('subject', 'slot_monitorings_subject_idx');
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
        Schema::dropIfExists('slot_monitorings');
    }
}
