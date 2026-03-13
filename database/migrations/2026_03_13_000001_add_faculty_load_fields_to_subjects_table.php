<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFacultyLoadFieldsToSubjectsTable extends Migration
{
    public function up()
    {
        Schema::table('subjects', function (Blueprint $table) {
            if (!Schema::hasColumn('subjects', 'faculty_id')) {
                $table->unsignedBigInteger('faculty_id')->nullable()->after('faculty');
            }

            if (!Schema::hasColumn('subjects', 'lec')) {
                $table->unsignedTinyInteger('lec')->default(0)->after('units');
            }

            if (!Schema::hasColumn('subjects', 'lab')) {
                $table->unsignedTinyInteger('lab')->default(0)->after('lec');
            }

            if (!Schema::hasColumn('subjects', 'load_type')) {
                $table->string('load_type')->nullable()->after('grading_status');
            }

            if (!Schema::hasColumn('subjects', 'credited_tuition_units')) {
                $table->decimal('credited_tuition_units', 5, 2)->nullable()->after('load_type');
            }

            if (!Schema::hasColumn('subjects', 'load_hours')) {
                $table->decimal('load_hours', 5, 2)->nullable()->after('credited_tuition_units');
            }

            if (!Schema::hasColumn('subjects', 'added_by')) {
                $table->string('added_by')->nullable()->after('load_hours');
            }
        });
    }

    public function down()
    {
        Schema::table('subjects', function (Blueprint $table) {
            $columns = [
                'faculty_id',
                'lec',
                'lab',
                'load_type',
                'credited_tuition_units',
                'load_hours',
                'added_by',
            ];

            foreach ($columns as $col) {
                if (Schema::hasColumn('subjects', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
