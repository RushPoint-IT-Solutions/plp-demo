<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateYearBlocksTable extends Migration
{
    public function up()
    {
        Schema::create('year_blocks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('label'); // e.g. "1-A"
        });

        // Insert default data right here — no seeder needed
        $blocks = [];
        foreach (['A', 'B'] as $section) {
            foreach ([1, 2, 3, 4] as $year) {
                $blocks[] = ['label' => $year . '-' . $section];
            }
        }
        DB::table('year_blocks')->insert($blocks);
    }

    public function down()
    {
        Schema::dropIfExists('year_blocks');
    }
}
