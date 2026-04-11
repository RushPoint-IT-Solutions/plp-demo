<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RemoveSeededFacultyNotificationDemo extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('portal_notifications')) {
            return;
        }

        $seededIds = DB::table('portal_notifications')
            ->where('source_reference', 'seed:registrar-evaluation-result-shared')
            ->pluck('id')
            ->all();

        if (empty($seededIds)) {
            return;
        }

        if (Schema::hasTable('notification_deliveries')) {
            DB::table('notification_deliveries')
                ->whereIn('portal_notification_id', $seededIds)
                ->delete();
        }

        DB::table('portal_notifications')
            ->whereIn('id', $seededIds)
            ->delete();
    }

    public function down()
    {
        // No-op: demo records should not be re-inserted.
    }
}
