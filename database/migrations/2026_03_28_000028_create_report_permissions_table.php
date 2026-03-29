<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportPermissionsTable extends Migration
{
    public function up()
    {
        Schema::create('report_permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('report_key');
            $table->string('report_type')->nullable();
            $table->boolean('is_allowed')->default(true);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['user_id', 'report_key']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('report_permissions');
    }
}
