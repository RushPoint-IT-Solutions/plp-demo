<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddMissingCourseDefaults extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('courses')) {
            return;
        }

        $defaults = [
            ['code' => 'BSCS', 'name' => 'Bachelor of Science in Computer Science'],
            ['code' => 'BSMT', 'name' => 'Bachelor of Science in Marine Transportation'],
        ];

        foreach ($defaults as $c) {
            $exists = DB::table('courses')->where('code', $c['code'])->exists();
            if (!$exists) {
                DB::table('courses')->insert($c);
            }
        }
    }

    public function down()
    {
        if (!Schema::hasTable('courses')) {
            return;
        }

        DB::table('courses')->whereIn('code', ['BSCS', 'BSMT'])->delete();
    }
}
