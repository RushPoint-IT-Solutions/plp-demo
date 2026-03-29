<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCertificatesIssuedTable extends Migration
{
    public function up()
    {
        Schema::create('certificates_issued', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id');
            $table->string('certificate_type');
            $table->string('purpose')->nullable();
            $table->date('date_issued')->nullable();
            $table->string('issued_by')->nullable();
            $table->string('school_year')->nullable();
            $table->string('semester')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('certificates_issued');
    }
}
