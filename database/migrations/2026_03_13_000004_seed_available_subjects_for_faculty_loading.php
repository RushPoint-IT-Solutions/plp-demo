<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SeedAvailableSubjectsForFacultyLoading extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('subjects') || !Schema::hasColumn('subjects', 'faculty_id')) {
            return;
        }

        $availableCount = DB::table('subjects')->whereNull('faculty_id')->count();
        if ($availableCount > 0) {
            return;
        }

        // Create “available subjects” by duplicating existing subjects but leaving them unassigned.
        // This keeps the dropdown populated using data we already have.
        $existing = DB::table('subjects')->limit(50)->get();
        if ($existing->isEmpty()) {
            return;
        }

        $now = now();
        $inserts = [];

        foreach ($existing as $row) {
            $inserts[] = [
                'code' => $row->code,
                'name' => $row->name,
                'units' => $row->units,
                'lec' => $row->lec ?? 0,
                'lab' => $row->lab ?? 0,
                'days' => $row->days,
                'time_start' => $row->time_start,
                'time_end' => $row->time_end,
                'room' => $row->room,
                'faculty' => 'TBA',
                'faculty_id' => null,
                'year_section' => $row->year_section,
                'course' => $row->course,
                'semester' => $row->semester,
                'school_year' => $row->school_year,
                'grading_status' => $row->grading_status ?? 'Open For Encoding',
                'load_type' => null,
                'credited_tuition_units' => null,
                'load_hours' => null,
                'added_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('subjects')->insert($inserts);
    }

    public function down()
    {
        if (!Schema::hasTable('subjects')) {
            return;
        }

        // Remove only the seeded unassigned duplicates.
        DB::table('subjects')->whereNull('faculty_id')->where('faculty', 'TBA')->delete();
    }
}
