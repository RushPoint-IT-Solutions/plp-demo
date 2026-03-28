<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterFacultyFilesTable extends Migration
{
    public function up()
    {
        Schema::create('master_faculty_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code', 60)->unique();
            $table->string('name', 190);
            $table->string('department', 190);
            $table->string('status', 30)->default('Active');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('master_faculty_files');
    }
}
