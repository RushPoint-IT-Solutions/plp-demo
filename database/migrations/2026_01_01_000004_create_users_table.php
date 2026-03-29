<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->string('username')->unique();
            $table->string('email')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('module')->default('student');
            $table->boolean('force_password_reset')->default(true);
            
            // Foreign Keys for roles
            $table->unsignedBigInteger('student_id')->nullable();
            $table->unsignedBigInteger('faculty_id')->nullable();
            $table->unsignedBigInteger('registrar_id')->nullable();
            $table->unsignedBigInteger('applicant_id')->nullable(); // Depending on if applicants will use accounts

            $table->rememberToken();
            $table->timestamps();

            // Setup foreign keys explicitly
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('faculty_id')->references('id')->on('faculties')->onDelete('cascade');
            $table->foreign('registrar_id')->references('id')->on('registrars')->onDelete('cascade');
            $table->foreign('applicant_id')->references('id')->on('applicants')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}
