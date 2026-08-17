<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserCourseScopesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('user_course_scopes')) {
            Schema::create('user_course_scopes', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('course_id');
                $table->timestamps();

                $table->unique(['user_id', 'course_id'], 'ucs_user_course_unique');
                $table->foreign('user_id', 'ucs_user_fk')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
                $table->foreign('course_id', 'ucs_course_fk')
                    ->references('id')
                    ->on('courses')
                    ->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('user_course_scopes')) {
            Schema::table('user_course_scopes', function (Blueprint $table) {
                $table->dropForeign('ucs_user_fk');
                $table->dropForeign('ucs_course_fk');
            });
            Schema::dropIfExists('user_course_scopes');
        }
    }
}
