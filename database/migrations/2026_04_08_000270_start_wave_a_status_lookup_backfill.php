<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StartWaveAStatusLookupBackfill extends Migration
{
    public function up()
    {
        $this->createLookupTables();
        $this->addForeignKeyColumns();
        $this->backfillLookupReferences();
        $this->addForeignKeys();
    }

    public function down()
    {
        $this->dropForeignKeys();
        $this->dropForeignKeyColumns();
        $this->dropLookupTables();
    }

    private function createLookupTables()
    {
        if (!Schema::hasTable('subject_grading_statuses')) {
            Schema::create('subject_grading_statuses', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('code', 120)->unique();
                $table->string('label', 120)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('announcement_types')) {
            Schema::create('announcement_types', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('code', 120)->unique();
                $table->string('label', 120)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('certificate_types')) {
            Schema::create('certificate_types', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('code', 120)->unique();
                $table->string('label', 120)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('report_types')) {
            Schema::create('report_types', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('code', 120)->unique();
                $table->string('label', 120)->nullable();
                $table->timestamps();
            });
        }
    }

    private function addForeignKeyColumns()
    {
        if (Schema::hasTable('subjects')
            && Schema::hasColumn('subjects', 'grading_status')
            && !Schema::hasColumn('subjects', 'grading_status_id')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->unsignedBigInteger('grading_status_id')->nullable()->after('grading_status');
                $table->index('grading_status_id', 'subjects_grading_status_id_idx');
            });
        }

        if (Schema::hasTable('system_announcements')
            && Schema::hasColumn('system_announcements', 'announcement_type')
            && !Schema::hasColumn('system_announcements', 'announcement_type_id')) {
            Schema::table('system_announcements', function (Blueprint $table) {
                $table->unsignedBigInteger('announcement_type_id')->nullable()->after('announcement_type');
                $table->index('announcement_type_id', 'system_announcements_announcement_type_id_idx');
            });
        }

        if (Schema::hasTable('certificates_issued')
            && Schema::hasColumn('certificates_issued', 'certificate_type')
            && !Schema::hasColumn('certificates_issued', 'certificate_type_id')) {
            Schema::table('certificates_issued', function (Blueprint $table) {
                $table->unsignedBigInteger('certificate_type_id')->nullable()->after('certificate_type');
                $table->index('certificate_type_id', 'certificates_issued_certificate_type_id_idx');
            });
        }

        if (Schema::hasTable('report_permissions')
            && Schema::hasColumn('report_permissions', 'report_type')
            && !Schema::hasColumn('report_permissions', 'report_type_id')) {
            Schema::table('report_permissions', function (Blueprint $table) {
                $table->unsignedBigInteger('report_type_id')->nullable()->after('report_type');
                $table->index('report_type_id', 'report_permissions_report_type_id_idx');
            });
        }
    }

    private function backfillLookupReferences()
    {
        $this->backfillSingleMapping('subjects', 'grading_status', 'subject_grading_statuses', 'grading_status_id');
        $this->backfillSingleMapping('system_announcements', 'announcement_type', 'announcement_types', 'announcement_type_id');
        $this->backfillSingleMapping('certificates_issued', 'certificate_type', 'certificate_types', 'certificate_type_id');
        $this->backfillSingleMapping('report_permissions', 'report_type', 'report_types', 'report_type_id');
    }

    private function addForeignKeys()
    {
        if (Schema::hasTable('subjects')
            && Schema::hasColumn('subjects', 'grading_status_id')
            && Schema::hasTable('subject_grading_statuses')
            && !$this->foreignKeyExists('subjects', 'subjects_grading_status_id_foreign')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->foreign('grading_status_id', 'subjects_grading_status_id_foreign')
                    ->references('id')
                    ->on('subject_grading_statuses')
                    ->onDelete('set null');
            });
        }

        if (Schema::hasTable('system_announcements')
            && Schema::hasColumn('system_announcements', 'announcement_type_id')
            && Schema::hasTable('announcement_types')
            && !$this->foreignKeyExists('system_announcements', 'system_announcements_announcement_type_id_foreign')) {
            Schema::table('system_announcements', function (Blueprint $table) {
                $table->foreign('announcement_type_id', 'system_announcements_announcement_type_id_foreign')
                    ->references('id')
                    ->on('announcement_types')
                    ->onDelete('set null');
            });
        }

        if (Schema::hasTable('certificates_issued')
            && Schema::hasColumn('certificates_issued', 'certificate_type_id')
            && Schema::hasTable('certificate_types')
            && !$this->foreignKeyExists('certificates_issued', 'certificates_issued_certificate_type_id_foreign')) {
            Schema::table('certificates_issued', function (Blueprint $table) {
                $table->foreign('certificate_type_id', 'certificates_issued_certificate_type_id_foreign')
                    ->references('id')
                    ->on('certificate_types')
                    ->onDelete('set null');
            });
        }

        if (Schema::hasTable('report_permissions')
            && Schema::hasColumn('report_permissions', 'report_type_id')
            && Schema::hasTable('report_types')
            && !$this->foreignKeyExists('report_permissions', 'report_permissions_report_type_id_foreign')) {
            Schema::table('report_permissions', function (Blueprint $table) {
                $table->foreign('report_type_id', 'report_permissions_report_type_id_foreign')
                    ->references('id')
                    ->on('report_types')
                    ->onDelete('set null');
            });
        }
    }

    private function dropForeignKeys()
    {
        if (Schema::hasTable('subjects')
            && Schema::hasColumn('subjects', 'grading_status_id')
            && $this->foreignKeyExists('subjects', 'subjects_grading_status_id_foreign')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->dropForeign('subjects_grading_status_id_foreign');
            });
        }

        if (Schema::hasTable('system_announcements')
            && Schema::hasColumn('system_announcements', 'announcement_type_id')
            && $this->foreignKeyExists('system_announcements', 'system_announcements_announcement_type_id_foreign')) {
            Schema::table('system_announcements', function (Blueprint $table) {
                $table->dropForeign('system_announcements_announcement_type_id_foreign');
            });
        }

        if (Schema::hasTable('certificates_issued')
            && Schema::hasColumn('certificates_issued', 'certificate_type_id')
            && $this->foreignKeyExists('certificates_issued', 'certificates_issued_certificate_type_id_foreign')) {
            Schema::table('certificates_issued', function (Blueprint $table) {
                $table->dropForeign('certificates_issued_certificate_type_id_foreign');
            });
        }

        if (Schema::hasTable('report_permissions')
            && Schema::hasColumn('report_permissions', 'report_type_id')
            && $this->foreignKeyExists('report_permissions', 'report_permissions_report_type_id_foreign')) {
            Schema::table('report_permissions', function (Blueprint $table) {
                $table->dropForeign('report_permissions_report_type_id_foreign');
            });
        }
    }

    private function dropForeignKeyColumns()
    {
        if (Schema::hasTable('subjects') && Schema::hasColumn('subjects', 'grading_status_id')) {
            Schema::table('subjects', function (Blueprint $table) {
                if ($this->indexExists('subjects', 'subjects_grading_status_id_idx')) {
                    $table->dropIndex('subjects_grading_status_id_idx');
                }
                $table->dropColumn('grading_status_id');
            });
        }

        if (Schema::hasTable('system_announcements') && Schema::hasColumn('system_announcements', 'announcement_type_id')) {
            Schema::table('system_announcements', function (Blueprint $table) {
                if ($this->indexExists('system_announcements', 'system_announcements_announcement_type_id_idx')) {
                    $table->dropIndex('system_announcements_announcement_type_id_idx');
                }
                $table->dropColumn('announcement_type_id');
            });
        }

        if (Schema::hasTable('certificates_issued') && Schema::hasColumn('certificates_issued', 'certificate_type_id')) {
            Schema::table('certificates_issued', function (Blueprint $table) {
                if ($this->indexExists('certificates_issued', 'certificates_issued_certificate_type_id_idx')) {
                    $table->dropIndex('certificates_issued_certificate_type_id_idx');
                }
                $table->dropColumn('certificate_type_id');
            });
        }

        if (Schema::hasTable('report_permissions') && Schema::hasColumn('report_permissions', 'report_type_id')) {
            Schema::table('report_permissions', function (Blueprint $table) {
                if ($this->indexExists('report_permissions', 'report_permissions_report_type_id_idx')) {
                    $table->dropIndex('report_permissions_report_type_id_idx');
                }
                $table->dropColumn('report_type_id');
            });
        }
    }

    private function dropLookupTables()
    {
        if (Schema::hasTable('report_types')) {
            Schema::drop('report_types');
        }

        if (Schema::hasTable('certificate_types')) {
            Schema::drop('certificate_types');
        }

        if (Schema::hasTable('announcement_types')) {
            Schema::drop('announcement_types');
        }

        if (Schema::hasTable('subject_grading_statuses')) {
            Schema::drop('subject_grading_statuses');
        }
    }

    private function backfillSingleMapping($sourceTable, $sourceColumn, $lookupTable, $foreignKeyColumn)
    {
        if (!Schema::hasTable($sourceTable)
            || !Schema::hasColumn($sourceTable, $sourceColumn)
            || !Schema::hasColumn($sourceTable, $foreignKeyColumn)
            || !Schema::hasTable($lookupTable)) {
            return;
        }

        $now = now();
        $rawValues = DB::table($sourceTable)
            ->select($sourceColumn)
            ->whereNotNull($sourceColumn)
            ->where($sourceColumn, '<>', '')
            ->distinct()
            ->pluck($sourceColumn);

        foreach ($rawValues as $value) {
            $normalized = trim((string) $value);
            if ($normalized === '') {
                continue;
            }

            $existing = DB::table($lookupTable)
                ->where('code', $normalized)
                ->first();

            if ($existing) {
                DB::table($lookupTable)
                    ->where('id', $existing->id)
                    ->update([
                        'label' => $normalized,
                        'updated_at' => $now,
                    ]);
            } else {
                DB::table($lookupTable)->insert([
                    'code' => $normalized,
                    'label' => $normalized,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $lookupMap = DB::table($lookupTable)
            ->pluck('id', 'code')
            ->toArray();

        DB::table($sourceTable)
            ->select('id', $sourceColumn, $foreignKeyColumn)
            ->whereNotNull($sourceColumn)
            ->where($sourceColumn, '<>', '')
            ->whereNull($foreignKeyColumn)
            ->orderBy('id')
            ->chunkById(500, function ($rows) use ($sourceColumn, $sourceTable, $foreignKeyColumn, $lookupMap) {
                foreach ($rows as $row) {
                    $normalized = trim((string) $row->{$sourceColumn});
                    if ($normalized === '' || !isset($lookupMap[$normalized])) {
                        continue;
                    }

                    DB::table($sourceTable)
                        ->where('id', $row->id)
                        ->update([$foreignKeyColumn => (int) $lookupMap[$normalized]]);
                }
            }, 'id');
    }

    private function indexExists($tableName, $indexName)
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

    private function foreignKeyExists($tableName, $foreignKeyName)
    {
        if (DB::getDriverName() !== 'mysql') {
            return false;
        }

        $databaseName = DB::getDatabaseName();
        if (!$databaseName) {
            return false;
        }

        return DB::table('information_schema.table_constraints')
            ->where('constraint_schema', $databaseName)
            ->where('table_name', $tableName)
            ->where('constraint_name', $foreignKeyName)
            ->where('constraint_type', 'FOREIGN KEY')
            ->exists();
    }
}
