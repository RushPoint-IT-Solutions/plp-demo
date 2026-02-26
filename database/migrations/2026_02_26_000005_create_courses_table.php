<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateCoursesTable extends Migration
{
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code');  // e.g. "BSIT"
            $table->string('name');  // e.g. "Bachelor of Science in Information Technology"
        });

        // Insert default data right here — no seeder needed
        DB::table('courses')->insert([
            ['code' => 'BSIT', 'name' => 'Bachelor of Science in Information Technology'],
            ['code' => 'BSBA', 'name' => 'Bachelor of Science in Business Administration'],
            ['code' => 'BSED', 'name' => 'Bachelor of Secondary Education'],
            ['code' => 'BEED', 'name' => 'Bachelor of Elementary Education'],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('courses');
    }
}
