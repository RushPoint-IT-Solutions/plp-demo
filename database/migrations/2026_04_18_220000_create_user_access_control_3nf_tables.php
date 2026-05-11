<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateUserAccessControl3nfTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('access_control_modules')) {
            Schema::create('access_control_modules', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('code', 60)->unique();
                $table->string('name', 120);
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index('parent_id', 'acm_parent_idx');
                $table->index('sort_order', 'acm_sort_idx');
                $table->index('is_active', 'acm_active_idx');

                $table->foreign('parent_id', 'acm_parent_fk')
                    ->references('id')
                    ->on('access_control_modules')
                    ->onDelete('set null');
            });
        }

        if (!Schema::hasTable('access_control_permission_types')) {
            Schema::create('access_control_permission_types', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('code', 40)->unique();
                $table->string('label', 80);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();

                $table->index('sort_order', 'acpt_sort_idx');
            });
        }

        if (!Schema::hasTable('user_access_controls')) {
            Schema::create('user_access_controls', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('access_control_module_id');
                $table->unsignedBigInteger('access_control_permission_type_id');
                $table->boolean('is_allowed')->default(false);
                $table->timestamps();

                $table->unique(
                    ['user_id', 'access_control_module_id', 'access_control_permission_type_id'],
                    'uac_user_module_perm_uniq'
                );

                $table->index('user_id', 'uac_user_idx');
                $table->index('access_control_module_id', 'uac_module_idx');
                $table->index('access_control_permission_type_id', 'uac_permission_type_idx');
                $table->index('is_allowed', 'uac_allowed_idx');

                $table->foreign('user_id', 'uac_user_fk')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');

                $table->foreign('access_control_module_id', 'uac_module_fk')
                    ->references('id')
                    ->on('access_control_modules')
                    ->onDelete('cascade');

                $table->foreign('access_control_permission_type_id', 'uac_permission_type_fk')
                    ->references('id')
                    ->on('access_control_permission_types')
                    ->onDelete('cascade');
            });
        }

        $this->seedAccessControlDefaults();
    }

    public function down()
    {
        if (Schema::hasTable('user_access_controls')) {
            Schema::table('user_access_controls', function (Blueprint $table) {
                $table->dropForeign('uac_user_fk');
                $table->dropForeign('uac_module_fk');
                $table->dropForeign('uac_permission_type_fk');
                $table->dropUnique('uac_user_module_perm_uniq');
                $table->dropIndex('uac_user_idx');
                $table->dropIndex('uac_module_idx');
                $table->dropIndex('uac_permission_type_idx');
                $table->dropIndex('uac_allowed_idx');
            });

            Schema::dropIfExists('user_access_controls');
        }

        if (Schema::hasTable('access_control_modules')) {
            Schema::table('access_control_modules', function (Blueprint $table) {
                $table->dropForeign('acm_parent_fk');
                $table->dropIndex('acm_parent_idx');
                $table->dropIndex('acm_sort_idx');
                $table->dropIndex('acm_active_idx');
            });
        }

        if (Schema::hasTable('access_control_permission_types')) {
            Schema::table('access_control_permission_types', function (Blueprint $table) {
                $table->dropIndex('acpt_sort_idx');
            });
        }

        Schema::dropIfExists('access_control_modules');
        Schema::dropIfExists('access_control_permission_types');
    }

    private function seedAccessControlDefaults()
    {
        if (!Schema::hasTable('access_control_modules') || !Schema::hasTable('access_control_permission_types')) {
            return;
        }

        $now = now();

        $moduleDefaults = [
            ['code' => 'admissions', 'name' => 'Admissions', 'sort_order' => 10],
            ['code' => 'student_records', 'name' => 'Student Records', 'sort_order' => 20],
            ['code' => 'academics', 'name' => 'Academics', 'sort_order' => 30],
            ['code' => 'faculty', 'name' => 'Faculty', 'sort_order' => 40],
            ['code' => 'documents_forms', 'name' => 'Documents & Forms', 'sort_order' => 50],
            ['code' => 'reports', 'name' => 'Reports', 'sort_order' => 60],
            ['code' => 'system', 'name' => 'System', 'sort_order' => 70],
        ];

        foreach ($moduleDefaults as $row) {
            DB::table('access_control_modules')->updateOrInsert(
                ['code' => $row['code']],
                [
                    'name' => $row['name'],
                    'parent_id' => null,
                    'sort_order' => $row['sort_order'],
                    'is_active' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        $permissionDefaults = [
            ['code' => 'view', 'label' => 'View', 'sort_order' => 10],
            ['code' => 'edit', 'label' => 'Edit', 'sort_order' => 20],
        ];

        foreach ($permissionDefaults as $row) {
            DB::table('access_control_permission_types')->updateOrInsert(
                ['code' => $row['code']],
                [
                    'label' => $row['label'],
                    'sort_order' => $row['sort_order'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}
