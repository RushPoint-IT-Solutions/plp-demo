<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubjectsTable extends Migration
{
    public function up()
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code');            // e.g. GEC19
            $table->string('name');            // e.g. Life and Works of Rizal
            $table->decimal('units', 3, 1);   // e.g. 2.0
            $table->string('days');            // e.g. "Sat" | "W,Th" | "M,F"
            $table->string('time_start');      // e.g. "04:00PM"
            $table->string('time_end');        // e.g. "07:00PM"
            $table->string('room');            // e.g. "RM 1"
            $table->string('faculty');         // e.g. "Abejo, M."
            $table->string('semester');        // e.g. "2nd Semester"
            $table->string('school_year');     // e.g. "2025-2026"
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('subjects');
    }
}
