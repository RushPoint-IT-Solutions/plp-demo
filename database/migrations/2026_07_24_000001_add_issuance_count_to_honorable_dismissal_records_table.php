<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddIssuanceCountToHonorableDismissalRecordsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('honorable_dismissal_records') || Schema::hasColumn('honorable_dismissal_records', 'issuance_count')) {
            return;
        }

        Schema::table('honorable_dismissal_records', function (Blueprint $table) {
            $table->unsignedInteger('issuance_count')->default(0)->after('status');
        });

        DB::table('honorable_dismissal_records')
            ->where('status', 'issued')
            ->where('issuance_count', 0)
            ->update(['issuance_count' => 1]);
    }

    public function down()
    {
        if (!Schema::hasTable('honorable_dismissal_records') || !Schema::hasColumn('honorable_dismissal_records', 'issuance_count')) {
            return;
        }

        Schema::table('honorable_dismissal_records', function (Blueprint $table) {
            $table->dropColumn('issuance_count');
        });
    }
}
