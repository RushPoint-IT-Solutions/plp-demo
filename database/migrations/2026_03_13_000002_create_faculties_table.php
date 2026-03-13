<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateFacultiesTable extends Migration
{
    public function up()
    {
        Schema::create('faculties', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });

        // Backfill from existing subjects.faculty strings (if any).
        if (Schema::hasTable('subjects') && Schema::hasColumn('subjects', 'faculty')) {
            $distinctFacultyNames = DB::table('subjects')
                ->select('faculty')
                ->whereNotNull('faculty')
                ->where('faculty', '!=', '')
                ->whereNotIn('faculty', ['TBA', 'Unassigned', 'N/A'])
                ->distinct()
                ->orderBy('faculty')
                ->pluck('faculty');

            $sequence = 1;
            $yearPrefix = (int) date('y');

            foreach ($distinctFacultyNames as $facultyName) {
                // Create a stable-ish code: YY-000001-XX
                $initials = $this->initialsFromName($facultyName);
                $code = sprintf('%02d-%06d-%s', $yearPrefix, $sequence, $initials);

                $facultyId = DB::table('faculties')->insertGetId([
                    'code' => $code,
                    'name' => $facultyName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if (Schema::hasColumn('subjects', 'faculty_id')) {
                    DB::table('subjects')
                        ->whereNull('faculty_id')
                        ->where('faculty', $facultyName)
                        ->update(['faculty_id' => $facultyId]);
                }

                $sequence++;
            }
        }
    }

    public function down()
    {
        Schema::dropIfExists('faculties');
    }

    private function initialsFromName(string $facultyName): string
    {
        // Expected formats: "Last, First" or "Last, First M.".
        $facultyName = trim($facultyName);
        if ($facultyName === '') return 'NA';

        $parts = array_map('trim', explode(',', $facultyName));
        $last = $parts[0] ?? '';
        $rest = $parts[1] ?? '';

        $lastInitial = $last !== '' ? strtoupper(substr($last, 0, 1)) : '';

        // Take the first letter of the first token after the comma.
        $restTokens = preg_split('/\s+/', trim($rest)) ?: [];
        $firstToken = $restTokens[0] ?? '';
        $firstInitial = $firstToken !== '' ? strtoupper(substr($firstToken, 0, 1)) : '';

        $initials = $firstInitial . $lastInitial;
        return $initials !== '' ? $initials : 'NA';
    }
}
