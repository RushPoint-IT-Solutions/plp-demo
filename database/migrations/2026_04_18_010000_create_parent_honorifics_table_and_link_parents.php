<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateParentHonorificsTableAndLinkParents extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('parent_honorifics')) {
            Schema::create('parent_honorifics', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('code', 30)->unique();
                $table->string('name', 80)->unique();
                $table->timestamps();
            });
        }

        $this->seedHonorificRows();

        if (Schema::hasTable('parents') && !Schema::hasColumn('parents', 'honorific_id')) {
            Schema::table('parents', function (Blueprint $table) {
                $table->unsignedBigInteger('honorific_id')->nullable()->after('parent_no');
                $table->index('honorific_id', 'parents_honorific_idx');
                $table->foreign('honorific_id', 'parents_honorific_fk')
                    ->references('id')
                    ->on('parent_honorifics')
                    ->onDelete('set null');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('parents') && Schema::hasColumn('parents', 'honorific_id')) {
            Schema::table('parents', function (Blueprint $table) {
                if ($this->foreignKeyExists('parents', 'parents_honorific_fk')) {
                    $table->dropForeign('parents_honorific_fk');
                }

                if ($this->indexExists('parents', 'parents_honorific_idx')) {
                    $table->dropIndex('parents_honorific_idx');
                }

                $table->dropColumn('honorific_id');
            });
        }

        if (Schema::hasTable('parent_honorifics')) {
            Schema::drop('parent_honorifics');
        }
    }

    private function seedHonorificRows()
    {
        if (!Schema::hasTable('parent_honorifics')) {
            return;
        }

        $now = now();

        $rows = [
            ['code' => 'MR', 'name' => 'Mr.'],
            ['code' => 'MRS', 'name' => 'Mrs.'],
            ['code' => 'MS', 'name' => 'Ms.'],
        ];

        foreach ($rows as $row) {
            DB::table('parent_honorifics')->updateOrInsert(
                ['code' => $row['code']],
                [
                    'name' => $row['name'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }

    private function foreignKeyExists($tableName, $constraintName)
    {
        $databaseName = DB::getDatabaseName();
        if (!$databaseName) {
            return false;
        }

        $result = DB::selectOne(
            'SELECT CONSTRAINT_NAME
             FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND CONSTRAINT_NAME = ?
               AND CONSTRAINT_TYPE = "FOREIGN KEY"
             LIMIT 1',
            [$databaseName, $tableName, $constraintName]
        );

        return !is_null($result);
    }

    private function indexExists($tableName, $indexName)
    {
        $databaseName = DB::getDatabaseName();
        if (!$databaseName) {
            return false;
        }

        $result = DB::selectOne(
            'SELECT INDEX_NAME
             FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND INDEX_NAME = ?
             LIMIT 1',
            [$databaseName, $tableName, $indexName]
        );

        return !is_null($result);
    }
}
