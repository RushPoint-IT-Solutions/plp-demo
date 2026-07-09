<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAccessControlRolesTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('access_control_roles')) {
            Schema::create('access_control_roles', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name', 120);
                $table->string('code', 60)->unique();
                $table->string('description', 255)->nullable();
                $table->boolean('is_system')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('role_access_controls')) {
            Schema::create('role_access_controls', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('role_id');
                $table->unsignedBigInteger('access_control_module_id');
                $table->unsignedBigInteger('access_control_permission_type_id');
                $table->boolean('is_allowed')->default(false);
                $table->timestamps();

                $table->unique(
                    ['role_id', 'access_control_module_id', 'access_control_permission_type_id'],
                    'rac_role_module_perm_uniq'
                );

                $table->index('role_id', 'rac_role_idx');
                $table->index('access_control_module_id', 'rac_module_idx');
                $table->index('access_control_permission_type_id', 'rac_permission_type_idx');
                $table->index('is_allowed', 'rac_allowed_idx');

                $table->foreign('role_id', 'rac_role_fk')
                    ->references('id')
                    ->on('access_control_roles')
                    ->onDelete('cascade');

                $table->foreign('access_control_module_id', 'rac_module_fk')
                    ->references('id')
                    ->on('access_control_modules')
                    ->onDelete('cascade');

                $table->foreign('access_control_permission_type_id', 'rac_permission_type_fk')
                    ->references('id')
                    ->on('access_control_permission_types')
                    ->onDelete('cascade');
            });
        }

        $this->seedStarterRoles();
    }

    public function down()
    {
        if (Schema::hasTable('role_access_controls')) {
            Schema::table('role_access_controls', function (Blueprint $table) {
                $table->dropForeign('rac_role_fk');
                $table->dropForeign('rac_module_fk');
                $table->dropForeign('rac_permission_type_fk');
            });

            Schema::dropIfExists('role_access_controls');
        }

        Schema::dropIfExists('access_control_roles');
    }

    private function seedStarterRoles()
    {
        $now = now();

        $roles = [
            [
                'name' => 'Administrator',
                'code' => 'administrator',
                'description' => 'Full access to all registrar modules and actions.',
                'is_system' => true,
            ],
            [
                'name' => 'Registrar Staff',
                'code' => 'registrar_staff',
                'description' => 'Default role for registrar staff accounts. Configure module permissions as needed.',
                'is_system' => false,
            ],
        ];

        foreach ($roles as $role) {
            DB::table('access_control_roles')->updateOrInsert(
                ['code' => $role['code']],
                [
                    'name' => $role['name'],
                    'description' => $role['description'],
                    'is_system' => $role['is_system'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}
