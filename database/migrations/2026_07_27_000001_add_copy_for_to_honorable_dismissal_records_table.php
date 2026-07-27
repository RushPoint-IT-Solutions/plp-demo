<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCopyForToHonorableDismissalRecordsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('honorable_dismissal_records') || Schema::hasColumn('honorable_dismissal_records', 'copy_for')) {
            return;
        }

        Schema::table('honorable_dismissal_records', function (Blueprint $table) {
            $table->text('copy_for')->nullable()->after('status');
        });
    }

    public function down()
    {
        if (!Schema::hasTable('honorable_dismissal_records') || !Schema::hasColumn('honorable_dismissal_records', 'copy_for')) {
            return;
        }

        Schema::table('honorable_dismissal_records', function (Blueprint $table) {
            $table->dropColumn('copy_for');
        });
    }
}
