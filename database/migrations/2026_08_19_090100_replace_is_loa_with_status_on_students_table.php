<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReplaceIsLoaWithStatusOnStudentsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('students')) {
            if (!Schema::hasColumn('students', 'status')) {
                Schema::table('students', function (Blueprint $table) {
                    $table->string('status')->default('Active')->after('withdrawn_remarks');
                    $table->date('status_date')->nullable()->after('status');
                    $table->string('status_remarks')->nullable()->after('status_date');
                });
            }

            if (Schema::hasColumn('students', 'is_loa')) {
                Schema::table('students', function (Blueprint $table) {
                    $table->dropColumn(['is_loa', 'loa_date', 'loa_remarks']);
                });
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('students')) {
            if (Schema::hasColumn('students', 'status')) {
                Schema::table('students', function (Blueprint $table) {
                    $table->dropColumn(['status', 'status_date', 'status_remarks']);
                });
            }

            if (!Schema::hasColumn('students', 'is_loa')) {
                Schema::table('students', function (Blueprint $table) {
                    $table->boolean('is_loa')->default(false)->after('withdrawn_remarks');
                    $table->date('loa_date')->nullable()->after('is_loa');
                    $table->string('loa_remarks')->nullable()->after('loa_date');
                });
            }
        }
    }
}
