<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLatinHonorToGraduateTaggingsTable extends Migration
{
    public function up()
    {
        Schema::table('graduate_taggings', function (Blueprint $table) {
            $table->string('latin_honor', 40)->nullable()->after('so_number');
        });
    }

    public function down()
    {
        Schema::table('graduate_taggings', function (Blueprint $table) {
            $table->dropColumn('latin_honor');
        });
    }
}
