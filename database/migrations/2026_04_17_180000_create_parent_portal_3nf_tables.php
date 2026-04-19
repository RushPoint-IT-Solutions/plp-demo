<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateParentPortal3nfTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('parent_relationship_types')) {
            Schema::create('parent_relationship_types', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('code', 40)->unique();
                $table->string('name', 80)->unique();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('parents')) {
            Schema::create('parents', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('parent_no', 30)->unique();
                $table->string('first_name', 120);
                $table->string('last_name', 120);
                $table->string('middle_name', 120)->nullable();
                $table->string('suffix', 30)->nullable();
                $table->string('email', 190)->nullable()->unique();
                $table->string('mobile_number', 30)->nullable();
                $table->timestamps();

                $table->index(['last_name', 'first_name'], 'parents_name_idx');
            });
        }

        if (!Schema::hasTable('parent_student_links')) {
            Schema::create('parent_student_links', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('parent_id');
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('relationship_type_id')->nullable();
                $table->boolean('is_primary_contact')->default(false);
                $table->boolean('receives_notifications')->default(true);
                $table->timestamps();

                $table->unique(['parent_id', 'student_id'], 'psl_parent_student_unique');
                $table->index('student_id', 'psl_student_idx');
                $table->index('relationship_type_id', 'psl_relationship_type_idx');

                $table->foreign('parent_id', 'psl_parent_fk')
                    ->references('id')
                    ->on('parents')
                    ->onDelete('cascade');

                $table->foreign('student_id', 'psl_student_fk')
                    ->references('id')
                    ->on('students')
                    ->onDelete('cascade');

                $table->foreign('relationship_type_id', 'psl_relationship_type_fk')
                    ->references('id')
                    ->on('parent_relationship_types')
                    ->onDelete('set null');
            });
        }

        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'parent_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('applicant_id');
                $table->index('parent_id', 'users_parent_idx');
                $table->foreign('parent_id', 'users_parent_fk')
                    ->references('id')
                    ->on('parents')
                    ->onDelete('cascade');
            });
        }

        $this->seedRelationshipTypes();
        $this->ensureUserAccountLookupRows();
    }

    public function down()
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'parent_id')) {
            Schema::table('users', function (Blueprint $table) {
                if ($this->foreignKeyExists('users', 'users_parent_fk')) {
                    $table->dropForeign('users_parent_fk');
                }

                if ($this->indexExists('users', 'users_parent_idx')) {
                    $table->dropIndex('users_parent_idx');
                }

                $table->dropColumn('parent_id');
            });
        }

        if (Schema::hasTable('parent_student_links')) {
            Schema::drop('parent_student_links');
        }

        if (Schema::hasTable('parents')) {
            Schema::drop('parents');
        }

        if (Schema::hasTable('parent_relationship_types')) {
            Schema::drop('parent_relationship_types');
        }
    }

    private function seedRelationshipTypes()
    {
        if (!Schema::hasTable('parent_relationship_types')) {
            return;
        }

        $now = now();

        $rows = [
            ['code' => 'MOTHER', 'name' => 'Mother'],
            ['code' => 'FATHER', 'name' => 'Father'],
            ['code' => 'GUARDIAN', 'name' => 'Guardian'],
            ['code' => 'SPONSOR', 'name' => 'Sponsor'],
        ];

        foreach ($rows as $row) {
            DB::table('parent_relationship_types')->updateOrInsert(
                ['code' => $row['code']],
                [
                    'name' => $row['name'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }

    private function ensureUserAccountLookupRows()
    {
        if (!Schema::hasTable('user_account_types') || !Schema::hasTable('user_account_states')) {
            return;
        }

        $now = now();

        DB::table('user_account_types')->updateOrInsert(
            ['code' => 'parent'],
            [
                'name' => 'Parent',
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        DB::table('user_account_states')->updateOrInsert(
            ['code' => 'active'],
            [
                'name' => 'Active',
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        DB::table('user_account_states')->updateOrInsert(
            ['code' => 'inactive'],
            [
                'name' => 'Inactive',
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
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
