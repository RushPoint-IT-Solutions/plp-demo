<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemAnnouncementsTable extends Migration
{
    public function up()
    {
        Schema::create('system_announcements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('date_from');
            $table->date('date_to');
            $table->string('title');
            $table->string('announcement_type', 40)->default('Everyone');
            $table->string('program', 120)->default('All Programs');
            $table->text('content');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('system_announcements');
    }
}
