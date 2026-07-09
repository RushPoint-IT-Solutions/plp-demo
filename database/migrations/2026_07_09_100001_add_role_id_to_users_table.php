<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRoleIdToUsersTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('users', 'access_control_role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('access_control_role_id')->nullable()->after('module');
                $table->index('access_control_role_id', 'users_access_control_role_idx');
                $table->foreign('access_control_role_id', 'users_access_control_role_fk')
                    ->references('id')
                    ->on('access_control_roles')
                    ->onDelete('set null');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('users', 'access_control_role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign('users_access_control_role_fk');
                $table->dropIndex('users_access_control_role_idx');
                $table->dropColumn('access_control_role_id');
            });
        }
    }
}
