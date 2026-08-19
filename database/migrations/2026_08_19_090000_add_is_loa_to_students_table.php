<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsLoaToStudentsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('students') && !Schema::hasColumn('students', 'is_loa')) {
            Schema::table('students', function (Blueprint $table) {
                $table->boolean('is_loa')->default(false)->after('withdrawn_remarks');
                $table->date('loa_date')->nullable()->after('is_loa');
                $table->string('loa_remarks')->nullable()->after('loa_date');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn(['is_loa', 'loa_date', 'loa_remarks']);
            });
        }
    }
}
