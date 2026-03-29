<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConfigPayloadToMasterFacultyFilesTable extends Migration
{
    public function up()
    {
        Schema::table('master_faculty_files', function (Blueprint $table) {
            $table->longText('config_payload')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('master_faculty_files', function (Blueprint $table) {
            $table->dropColumn('config_payload');
        });
    }
}
