<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanupPlaceholderFaculties extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('faculties') || !Schema::hasTable('subjects') || !Schema::hasColumn('subjects', 'faculty_id')) {
            return;
        }

        $placeholders = ['TBA', 'Unassigned', 'N/A'];

        $placeholderIds = DB::table('faculties')
            ->whereIn('name', $placeholders)
            ->pluck('id');

        if ($placeholderIds->isEmpty()) {
            return;
        }

        DB::table('subjects')
            ->whereIn('faculty_id', $placeholderIds)
            ->update(['faculty_id' => null]);

        DB::table('faculties')->whereIn('id', $placeholderIds)->delete();
    }

    public function down()
    {
        // No-op: we don't want to recreate placeholder faculty records.
    }
}
