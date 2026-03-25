<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrUpdateRegistrarsAndUserModuleLinks extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('registrars')) {
            Schema::create('registrars', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('code')->unique();
                $table->string('name');
                $table->string('email')->nullable()->unique();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'faculty_id')) {
                $table->unsignedBigInteger('faculty_id')->nullable()->after('student_id');
            }

            if (!Schema::hasColumn('users', 'registrar_id')) {
                $table->unsignedBigInteger('registrar_id')->nullable()->after('faculty_id');
            }
        });
    }

    public function down()
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'registrar_id')) {
                    $table->dropColumn('registrar_id');
                }

                if (Schema::hasColumn('users', 'faculty_id')) {
                    $table->dropColumn('faculty_id');
                }
            });
        }

        Schema::dropIfExists('registrars');
    }
}
