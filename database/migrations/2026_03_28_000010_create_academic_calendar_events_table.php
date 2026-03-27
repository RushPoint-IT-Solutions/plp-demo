<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAcademicCalendarEventsTable extends Migration
{
    public function up()
    {
        Schema::create('academic_calendar_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('event_date');
            $table->time('time_from')->nullable();
            $table->time('time_to')->nullable();
            $table->string('title');
            $table->string('venue')->nullable();
            $table->string('in_charge')->nullable();
            $table->date('post_until')->nullable();
            $table->string('event_type')->default('event'); // event | holiday
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['event_date', 'is_active']);
            $table->index('event_type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('academic_calendar_events');
    }
}
