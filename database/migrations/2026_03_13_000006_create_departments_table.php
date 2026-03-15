<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateDepartmentsTable extends Migration
{
    public function up()
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code')->unique();
            $table->string('description')->unique();
            $table->timestamps();
        });

        DB::table('departments')->insert([
            [
                'code' => '112',
                'description' => 'College of Information Technology',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => '113',
                'description' => 'College of Engineering',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => '114',
                'description' => 'College of Education',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('departments');
    }
}
