<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddPermissionTypesCreateDeletePrintApprove extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('access_control_permission_types')) {
            return;
        }

        $now = now();

        $permissionDefaults = [
            ['code' => 'create', 'label' => 'Create', 'sort_order' => 30],
            ['code' => 'delete', 'label' => 'Delete', 'sort_order' => 40],
            ['code' => 'print', 'label' => 'Print', 'sort_order' => 50],
            ['code' => 'approve', 'label' => 'Approve', 'sort_order' => 60],
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

    public function down()
    {
        if (!Schema::hasTable('access_control_permission_types')) {
            return;
        }

        DB::table('access_control_permission_types')
            ->whereIn('code', ['create', 'delete', 'print', 'approve'])
            ->delete();
    }
}
