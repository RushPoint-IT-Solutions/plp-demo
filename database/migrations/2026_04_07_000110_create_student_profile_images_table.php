<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentProfileImagesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('student_profile_images')) {
            return;
        }

        Schema::create('student_profile_images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_profile_id');
            $table->unsignedBigInteger('uploaded_by_user_id')->nullable();
            $table->string('original_filename', 190);
            $table->string('storage_disk', 40)->default('public');
            $table->string('storage_path', 255);
            $table->string('mime_type', 120)->nullable();
            $table->unsignedInteger('size_bytes')->default(0);
            $table->timestamps();

            $table->unique('student_profile_id');
            $table->index('uploaded_by_user_id');
            $table->index('updated_at');

            $table->foreign('student_profile_id')
                ->references('id')
                ->on('student_profiles')
                ->onDelete('cascade');

            $table->foreign('uploaded_by_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_profile_images');
    }
}
