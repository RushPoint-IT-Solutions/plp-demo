<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSemestersTable extends Migration
{
    public function up()
    {
        Schema::create('semesters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name'); // e.g. "1st Semester"
        });

        // Insert default data right here — no seeder needed
        DB::table('semesters')->insert([
            ['name' => '1st Semester'],
            ['name' => '2nd Semester'],
            ['name' => 'Summer'],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('semesters');
    }
}
