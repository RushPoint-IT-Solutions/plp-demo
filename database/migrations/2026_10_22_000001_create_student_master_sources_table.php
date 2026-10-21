<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentMasterSourcesTable extends Migration
{
    public function up()
    {
        Schema::create('student_master_sources', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id')->unique();
            $table->boolean('is_active');
            $table->string('student_type_code')->nullable();
            $table->unsignedInteger('source_row');
            $table->string('file_sha256', 64);
            $table->longText('source_data');
            $table->timestamps();
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_master_sources');
    }
}
