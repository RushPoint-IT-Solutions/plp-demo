<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSuspendRemarksToGraduateTaggingsTable extends Migration
{
    public function up()
    {
        Schema::table('graduate_taggings', function (Blueprint $table) {
            $table->string('suspend_remarks', 255)->nullable()->after('suspend_account');
        });
    }

    public function down()
    {
        Schema::table('graduate_taggings', function (Blueprint $table) {
            $table->dropColumn('suspend_remarks');
        });
    }
}
