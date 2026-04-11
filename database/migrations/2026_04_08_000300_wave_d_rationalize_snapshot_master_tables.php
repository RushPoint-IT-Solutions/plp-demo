<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WaveDRationalizeSnapshotMasterTables extends Migration
{
    public function up()
    {
        $this->addSnapshotColumns();
        $this->backfillSnapshotColumns();
        $this->addSnapshotForeignKeys();
    }

    public function down()
    {
        $this->dropSnapshotForeignKeys();
        $this->dropSnapshotColumns();
    }

    private function addSnapshotColumns()
    {
        if (Schema::hasTable('master_student_profiles')) {
            Schema::table('master_student_profiles', function (Blueprint $table) {
                if (!Schema::hasColumn('master_student_profiles', 'source_student_id')) {
                    $table->unsignedBigInteger('source_student_id')->nullable()->after('student_no');
                    $table->index('source_student_id', 'msp_source_student_id_idx');
                }
                if (!Schema::hasColumn('master_student_profiles', 'snapshot_taken_at')) {
                    $table->timestamp('snapshot_taken_at')->nullable()->after('year_level');
                }
                if (!Schema::hasColumn('master_student_profiles', 'snapshot_note')) {
                    $table->string('snapshot_note', 255)->nullable()->after('snapshot_taken_at');
                }
                if (!Schema::hasColumn('master_student_profiles', 'is_snapshot')) {
                    $table->boolean('is_snapshot')->default(true)->after('snapshot_note');
                }
            });
        }

        if (Schema::hasTable('master_student_grade_files')) {
            Schema::table('master_student_grade_files', function (Blueprint $table) {
                if (!Schema::hasColumn('master_student_grade_files', 'source_student_id')) {
                    $table->unsignedBigInteger('source_student_id')->nullable()->after('student_no');
                    $table->index('source_student_id', 'msgf_source_student_id_idx');
                }
                if (!Schema::hasColumn('master_student_grade_files', 'snapshot_taken_at')) {
                    $table->timestamp('snapshot_taken_at')->nullable()->after('year_level');
                }
                if (!Schema::hasColumn('master_student_grade_files', 'snapshot_note')) {
                    $table->string('snapshot_note', 255)->nullable()->after('snapshot_taken_at');
                }
                if (!Schema::hasColumn('master_student_grade_files', 'is_snapshot')) {
                    $table->boolean('is_snapshot')->default(true)->after('snapshot_note');
                }
            });
        }

        if (Schema::hasTable('master_faculty_files')) {
            Schema::table('master_faculty_files', function (Blueprint $table) {
                if (!Schema::hasColumn('master_faculty_files', 'source_faculty_id')) {
                    $table->unsignedBigInteger('source_faculty_id')->nullable()->after('code');
                    $table->index('source_faculty_id', 'mff_source_faculty_id_idx');
                }
                if (!Schema::hasColumn('master_faculty_files', 'snapshot_taken_at')) {
                    $table->timestamp('snapshot_taken_at')->nullable()->after('config_payload');
                }
                if (!Schema::hasColumn('master_faculty_files', 'snapshot_note')) {
                    $table->string('snapshot_note', 255)->nullable()->after('snapshot_taken_at');
                }
                if (!Schema::hasColumn('master_faculty_files', 'is_snapshot')) {
                    $table->boolean('is_snapshot')->default(true)->after('snapshot_note');
                }
            });
        }
    }

    private function backfillSnapshotColumns()
    {
        if (Schema::hasTable('master_student_profiles') && Schema::hasTable('students')) {
            $rows = DB::table('master_student_profiles')
                ->select('id', 'student_no', 'created_at')
                ->whereNull('source_student_id')
                ->get();

            foreach ($rows as $row) {
                $sourceId = DB::table('students')
                    ->where('student_no', $row->student_no)
                    ->value('id');

                DB::table('master_student_profiles')
                    ->where('id', $row->id)
                    ->update([
                        'source_student_id' => $sourceId ?: null,
                        'snapshot_taken_at' => $row->created_at ?: now(),
                        'is_snapshot' => true,
                    ]);
            }

            DB::table('master_student_profiles')
                ->whereNull('snapshot_taken_at')
                ->update(['snapshot_taken_at' => now()]);

            DB::table('master_student_profiles')
                ->whereNull('is_snapshot')
                ->update(['is_snapshot' => true]);
        }

        if (Schema::hasTable('master_student_grade_files') && Schema::hasTable('students')) {
            $rows = DB::table('master_student_grade_files')
                ->select('id', 'student_no', 'created_at')
                ->whereNull('source_student_id')
                ->get();

            foreach ($rows as $row) {
                $sourceId = DB::table('students')
                    ->where('student_no', $row->student_no)
                    ->value('id');

                DB::table('master_student_grade_files')
                    ->where('id', $row->id)
                    ->update([
                        'source_student_id' => $sourceId ?: null,
                        'snapshot_taken_at' => $row->created_at ?: now(),
                        'is_snapshot' => true,
                    ]);
            }

            DB::table('master_student_grade_files')
                ->whereNull('snapshot_taken_at')
                ->update(['snapshot_taken_at' => now()]);

            DB::table('master_student_grade_files')
                ->whereNull('is_snapshot')
                ->update(['is_snapshot' => true]);
        }

        if (Schema::hasTable('master_faculty_files') && Schema::hasTable('faculties')) {
            $rows = DB::table('master_faculty_files')
                ->select('id', 'code', 'name', 'created_at')
                ->whereNull('source_faculty_id')
                ->get();

            foreach ($rows as $row) {
                $sourceId = DB::table('faculties')
                    ->where('code', $row->code)
                    ->value('id');

                if (!$sourceId && $row->name) {
                    $sourceId = DB::table('faculties')
                        ->where('name', $row->name)
                        ->value('id');
                }

                DB::table('master_faculty_files')
                    ->where('id', $row->id)
                    ->update([
                        'source_faculty_id' => $sourceId ?: null,
                        'snapshot_taken_at' => $row->created_at ?: now(),
                        'is_snapshot' => true,
                    ]);
            }

            DB::table('master_faculty_files')
                ->whereNull('snapshot_taken_at')
                ->update(['snapshot_taken_at' => now()]);

            DB::table('master_faculty_files')
                ->whereNull('is_snapshot')
                ->update(['is_snapshot' => true]);
        }
    }

    private function addSnapshotForeignKeys()
    {
        if (Schema::hasTable('master_student_profiles')
            && Schema::hasTable('students')
            && Schema::hasColumn('master_student_profiles', 'source_student_id')
            && !$this->foreignKeyExists('master_student_profiles', 'msp_source_student_id_foreign')) {
            Schema::table('master_student_profiles', function (Blueprint $table) {
                $table->foreign('source_student_id', 'msp_source_student_id_foreign')
                    ->references('id')
                    ->on('students')
                    ->onDelete('set null');
            });
        }

        if (Schema::hasTable('master_student_grade_files')
            && Schema::hasTable('students')
            && Schema::hasColumn('master_student_grade_files', 'source_student_id')
            && !$this->foreignKeyExists('master_student_grade_files', 'msgf_source_student_id_foreign')) {
            Schema::table('master_student_grade_files', function (Blueprint $table) {
                $table->foreign('source_student_id', 'msgf_source_student_id_foreign')
                    ->references('id')
                    ->on('students')
                    ->onDelete('set null');
            });
        }

        if (Schema::hasTable('master_faculty_files')
            && Schema::hasTable('faculties')
            && Schema::hasColumn('master_faculty_files', 'source_faculty_id')
            && !$this->foreignKeyExists('master_faculty_files', 'mff_source_faculty_id_foreign')) {
            Schema::table('master_faculty_files', function (Blueprint $table) {
                $table->foreign('source_faculty_id', 'mff_source_faculty_id_foreign')
                    ->references('id')
                    ->on('faculties')
                    ->onDelete('set null');
            });
        }
    }

    private function dropSnapshotForeignKeys()
    {
        if (Schema::hasTable('master_student_profiles')
            && Schema::hasColumn('master_student_profiles', 'source_student_id')
            && $this->foreignKeyExists('master_student_profiles', 'msp_source_student_id_foreign')) {
            Schema::table('master_student_profiles', function (Blueprint $table) {
                $table->dropForeign('msp_source_student_id_foreign');
            });
        }

        if (Schema::hasTable('master_student_grade_files')
            && Schema::hasColumn('master_student_grade_files', 'source_student_id')
            && $this->foreignKeyExists('master_student_grade_files', 'msgf_source_student_id_foreign')) {
            Schema::table('master_student_grade_files', function (Blueprint $table) {
                $table->dropForeign('msgf_source_student_id_foreign');
            });
        }

        if (Schema::hasTable('master_faculty_files')
            && Schema::hasColumn('master_faculty_files', 'source_faculty_id')
            && $this->foreignKeyExists('master_faculty_files', 'mff_source_faculty_id_foreign')) {
            Schema::table('master_faculty_files', function (Blueprint $table) {
                $table->dropForeign('mff_source_faculty_id_foreign');
            });
        }
    }

    private function dropSnapshotColumns()
    {
        if (Schema::hasTable('master_student_profiles')) {
            Schema::table('master_student_profiles', function (Blueprint $table) {
                if ($this->indexExists('master_student_profiles', 'msp_source_student_id_idx')) {
                    $table->dropIndex('msp_source_student_id_idx');
                }

                $columns = [];
                foreach (['source_student_id', 'snapshot_taken_at', 'snapshot_note', 'is_snapshot'] as $column) {
                    if (Schema::hasColumn('master_student_profiles', $column)) {
                        $columns[] = $column;
                    }
                }

                if (count($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }

        if (Schema::hasTable('master_student_grade_files')) {
            Schema::table('master_student_grade_files', function (Blueprint $table) {
                if ($this->indexExists('master_student_grade_files', 'msgf_source_student_id_idx')) {
                    $table->dropIndex('msgf_source_student_id_idx');
                }

                $columns = [];
                foreach (['source_student_id', 'snapshot_taken_at', 'snapshot_note', 'is_snapshot'] as $column) {
                    if (Schema::hasColumn('master_student_grade_files', $column)) {
                        $columns[] = $column;
                    }
                }

                if (count($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }

        if (Schema::hasTable('master_faculty_files')) {
            Schema::table('master_faculty_files', function (Blueprint $table) {
                if ($this->indexExists('master_faculty_files', 'mff_source_faculty_id_idx')) {
                    $table->dropIndex('mff_source_faculty_id_idx');
                }

                $columns = [];
                foreach (['source_faculty_id', 'snapshot_taken_at', 'snapshot_note', 'is_snapshot'] as $column) {
                    if (Schema::hasColumn('master_faculty_files', $column)) {
                        $columns[] = $column;
                    }
                }

                if (count($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }
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
}
