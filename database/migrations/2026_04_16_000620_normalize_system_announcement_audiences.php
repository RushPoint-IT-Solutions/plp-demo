<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NormalizeSystemAnnouncementAudiences extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('announcement_types') || !Schema::hasTable('system_announcements')) {
            return;
        }

        $canonicalIds = $this->ensureCanonicalAudienceTypes();
        $this->normalizeAudienceReferences($canonicalIds);
        $this->addVisibilityIndex();
    }

    public function down()
    {
        $this->dropVisibilityIndex();
    }

    private function ensureCanonicalAudienceTypes(): array
    {
        $canonicalRows = [
            'everyone' => 'Everyone',
            'students' => 'Students',
            'faculty' => 'Faculty',
            'staff' => 'Staff',
            'applicant' => 'Applicant',
        ];

        $now = now();
        $ids = [];

        foreach ($canonicalRows as $code => $label) {
            $existing = DB::table('announcement_types')
                ->whereRaw('LOWER(TRIM(code)) = ?', [$code])
                ->first();

            if ($existing) {
                DB::table('announcement_types')
                    ->where('id', $existing->id)
                    ->update([
                        'code' => $code,
                        'label' => $label,
                        'updated_at' => $now,
                    ]);

                $ids[$code] = (int) $existing->id;
                continue;
            }

            $id = DB::table('announcement_types')->insertGetId([
                'code' => $code,
                'label' => $label,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $ids[$code] = (int) $id;
        }

        return $ids;
    }

    private function normalizeAudienceReferences(array $canonicalIds): void
    {
        if (!Schema::hasColumn('system_announcements', 'announcement_type_id')) {
            return;
        }

        $aliasGroups = [
            'everyone' => ['everyone', 'all', 'all users', 'all user', 'all audience', 'general'],
            'students' => ['students', 'student'],
            'faculty' => ['faculty', 'teacher', 'teachers', 'instructor', 'instructors'],
            'staff' => ['staff', 'registrar', 'admin', 'administrator', 'administrators'],
            'applicant' => ['applicant', 'applicants'],
        ];

        foreach ($aliasGroups as $canonicalCode => $aliases) {
            if (!isset($canonicalIds[$canonicalCode])) {
                continue;
            }

            $legacyIds = DB::table('announcement_types')
                ->where(function ($query) use ($aliases) {
                    foreach ($aliases as $alias) {
                        $query->orWhereRaw('LOWER(TRIM(code)) = ?', [$alias]);
                    }
                })
                ->pluck('id')
                ->map(function ($id) {
                    return (int) $id;
                })
                ->filter()
                ->values()
                ->all();

            if (empty($legacyIds)) {
                continue;
            }

            DB::table('system_announcements')
                ->whereIn('announcement_type_id', $legacyIds)
                ->update(['announcement_type_id' => (int) $canonicalIds[$canonicalCode]]);
        }

        if (isset($canonicalIds['everyone'])) {
            DB::table('system_announcements')
                ->whereNull('announcement_type_id')
                ->update(['announcement_type_id' => (int) $canonicalIds['everyone']]);
        }
    }

    private function addVisibilityIndex(): void
    {
        if (!Schema::hasTable('system_announcements')
            || !Schema::hasColumn('system_announcements', 'announcement_type_id')
            || !Schema::hasColumn('system_announcements', 'date_from')
            || !Schema::hasColumn('system_announcements', 'date_to')) {
            return;
        }

        $indexName = 'system_announcements_audience_window_idx';
        if ($this->indexExists('system_announcements', $indexName)) {
            return;
        }

        Schema::table('system_announcements', function (Blueprint $table) use ($indexName) {
            $table->index(['announcement_type_id', 'date_from', 'date_to'], $indexName);
        });
    }

    private function dropVisibilityIndex(): void
    {
        if (!Schema::hasTable('system_announcements')) {
            return;
        }

        $indexName = 'system_announcements_audience_window_idx';
        if (!$this->indexExists('system_announcements', $indexName)) {
            return;
        }

        Schema::table('system_announcements', function (Blueprint $table) use ($indexName) {
            $table->dropIndex($indexName);
        });
    }

    private function indexExists($tableName, $indexName): bool
    {
        if (DB::getDriverName() !== 'mysql') {
            return false;
        }

        $databaseName = DB::getDatabaseName();
        if (!$databaseName) {
            return false;
        }

        return DB::table('information_schema.statistics')
            ->where('table_schema', $databaseName)
            ->where('table_name', $tableName)
            ->where('index_name', $indexName)
            ->exists();
    }
}
