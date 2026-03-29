<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlumniTrackerSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('alumni_tracker_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('school_year', 30);
            $table->string('term', 30);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('alumni_tracker_settings');
    }
}
