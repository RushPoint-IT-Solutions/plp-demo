<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsWithdrawnToStudentsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('students') && !Schema::hasColumn('students', 'is_withdrawn')) {
            Schema::table('students', function (Blueprint $table) {
                $table->boolean('is_withdrawn')->default(false)->after('year_block_id');
                $table->date('withdrawn_date')->nullable()->after('is_withdrawn');
                $table->string('withdrawn_remarks')->nullable()->after('withdrawn_date');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn(['is_withdrawn', 'withdrawn_date', 'withdrawn_remarks']);
            });
        }
    }
}
