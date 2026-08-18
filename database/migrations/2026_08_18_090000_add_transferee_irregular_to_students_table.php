<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTransfereeIrregularToStudentsTable extends Migration
{
    public function up()
    {
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'is_transferee')) {
                $table->boolean('is_transferee')->default(false)->after('is_withdrawn');
            }
            if (!Schema::hasColumn('students', 'is_irregular')) {
                $table->boolean('is_irregular')->default(false)->after('is_transferee');
            }
        });
    }

    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'is_irregular')) {
                $table->dropColumn('is_irregular');
            }
            if (Schema::hasColumn('students', 'is_transferee')) {
                $table->dropColumn('is_transferee');
            }
        });
    }
}
