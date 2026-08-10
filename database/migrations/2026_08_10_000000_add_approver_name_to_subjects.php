<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApproverNameToSubjects extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('subjects')) {
            return;
        }

        Schema::table('subjects', function (Blueprint $table) {
            if (!Schema::hasColumn('subjects', 'dean_approved_by_name')) {
                $table->string('dean_approved_by_name', 150)->nullable()->after('dean_approved_at');
            }
            if (!Schema::hasColumn('subjects', 'registrar_finalized_by_name')) {
                $table->string('registrar_finalized_by_name', 150)->nullable()->after('registrar_finalized_at');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('subjects')) {
            return;
        }

        Schema::table('subjects', function (Blueprint $table) {
            foreach (['dean_approved_by_name', 'registrar_finalized_by_name'] as $column) {
                if (Schema::hasColumn('subjects', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
