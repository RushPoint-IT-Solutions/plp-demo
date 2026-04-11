<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NormalizeAddressDimensions extends Migration
{
    public function up()
    {
        $this->createAddressTable();
        $this->addAddressColumns();
        $this->backfillAddressColumns();
        $this->addAddressForeignKeys();
    }

    public function down()
    {
        $this->dropAddressForeignKeys();
        $this->dropAddressColumns();

        if (Schema::hasTable('location_addresses')) {
            Schema::drop('location_addresses');
        }
    }

    private function createAddressTable()
    {
        if (Schema::hasTable('location_addresses')) {
            return;
        }

        Schema::create('location_addresses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('street', 255)->nullable();
            $table->string('barangay', 255)->nullable();
            $table->string('municipality', 255)->nullable();
            $table->string('province', 255)->nullable();
            $table->string('region', 255)->nullable();
            $table->string('zipcode', 20)->nullable();
            $table->string('canonical_key', 600)->unique();
            $table->timestamps();
        });
    }

    private function addAddressColumns()
    {
        $this->addAddressColumnsForTable('applicants');
        $this->addAddressColumnsForTable('student_profiles');
    }

    private function addAddressColumnsForTable($tableName)
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'present_location_address_id')) {
                $table->unsignedBigInteger('present_location_address_id')->nullable()->after('present_region');
                $table->index('present_location_address_id', $tableName . '_present_location_address_id_idx');
            }

            if (!Schema::hasColumn($tableName, 'permanent_location_address_id')) {
                $table->unsignedBigInteger('permanent_location_address_id')->nullable()->after('permanent_region');
                $table->index('permanent_location_address_id', $tableName . '_permanent_location_address_id_idx');
            }
        });
    }

    private function backfillAddressColumns()
    {
        $this->backfillAddressForTable('applicants');
        $this->backfillAddressForTable('student_profiles');
    }

    private function backfillAddressForTable($tableName)
    {
        if (!Schema::hasTable($tableName)
            || !Schema::hasColumn($tableName, 'present_location_address_id')
            || !Schema::hasColumn($tableName, 'permanent_location_address_id')) {
            return;
        }

        $rows = DB::table($tableName)
            ->select([
                'id',
                'present_street',
                'present_barangay',
                'present_municipality',
                'present_province',
                'present_region',
                'present_zipcode',
                'permanent_street',
                'permanent_barangay',
                'permanent_municipality',
                'permanent_province',
                'permanent_region',
                'permanent_zipcode',
            ])
            ->orderBy('id')
            ->get();

        foreach ($rows as $row) {
            $presentId = $this->resolveAddressId([
                'street' => $row->present_street,
                'barangay' => $row->present_barangay,
                'municipality' => $row->present_municipality,
                'province' => $row->present_province,
                'region' => $row->present_region,
                'zipcode' => $row->present_zipcode,
            ]);

            $permanentId = $this->resolveAddressId([
                'street' => $row->permanent_street,
                'barangay' => $row->permanent_barangay,
                'municipality' => $row->permanent_municipality,
                'province' => $row->permanent_province,
                'region' => $row->permanent_region,
                'zipcode' => $row->permanent_zipcode,
            ]);

            DB::table($tableName)
                ->where('id', $row->id)
                ->update([
                    'present_location_address_id' => $presentId,
                    'permanent_location_address_id' => $permanentId,
                ]);
        }
    }

    private function resolveAddressId(array $parts)
    {
        $canonical = $this->canonicalAddressKey($parts);
        if ($canonical === '') {
            return null;
        }

        $existing = DB::table('location_addresses')
            ->where('canonical_key', $canonical)
            ->first();

        if ($existing) {
            return (int) $existing->id;
        }

        DB::table('location_addresses')->insert([
            'street' => $this->nullableTrim($parts['street'] ?? null),
            'barangay' => $this->nullableTrim($parts['barangay'] ?? null),
            'municipality' => $this->nullableTrim($parts['municipality'] ?? null),
            'province' => $this->nullableTrim($parts['province'] ?? null),
            'region' => $this->nullableTrim($parts['region'] ?? null),
            'zipcode' => $this->nullableTrim($parts['zipcode'] ?? null),
            'canonical_key' => $canonical,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return (int) DB::getPdo()->lastInsertId();
    }

    private function addAddressForeignKeys()
    {
        $this->addAddressForeignKeysForTable('applicants');
        $this->addAddressForeignKeysForTable('student_profiles');
    }

    private function addAddressForeignKeysForTable($tableName)
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        $presentForeign = $tableName . '_present_location_address_id_foreign';
        $permanentForeign = $tableName . '_permanent_location_address_id_foreign';

        if (Schema::hasColumn($tableName, 'present_location_address_id')
            && !$this->foreignKeyExists($tableName, $presentForeign)) {
            Schema::table($tableName, function (Blueprint $table) use ($presentForeign) {
                $table->foreign('present_location_address_id', $presentForeign)
                    ->references('id')
                    ->on('location_addresses')
                    ->onDelete('set null');
            });
        }

        if (Schema::hasColumn($tableName, 'permanent_location_address_id')
            && !$this->foreignKeyExists($tableName, $permanentForeign)) {
            Schema::table($tableName, function (Blueprint $table) use ($permanentForeign) {
                $table->foreign('permanent_location_address_id', $permanentForeign)
                    ->references('id')
                    ->on('location_addresses')
                    ->onDelete('set null');
            });
        }
    }

    private function dropAddressForeignKeys()
    {
        $this->dropAddressForeignKeysForTable('applicants');
        $this->dropAddressForeignKeysForTable('student_profiles');
    }

    private function dropAddressForeignKeysForTable($tableName)
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        $presentForeign = $tableName . '_present_location_address_id_foreign';
        $permanentForeign = $tableName . '_permanent_location_address_id_foreign';

        if ($this->foreignKeyExists($tableName, $presentForeign)) {
            Schema::table($tableName, function (Blueprint $table) use ($presentForeign) {
                $table->dropForeign($presentForeign);
            });
        }

        if ($this->foreignKeyExists($tableName, $permanentForeign)) {
            Schema::table($tableName, function (Blueprint $table) use ($permanentForeign) {
                $table->dropForeign($permanentForeign);
            });
        }
    }

    private function dropAddressColumns()
    {
        $this->dropAddressColumnsForTable('applicants');
        $this->dropAddressColumnsForTable('student_profiles');
    }

    private function dropAddressColumnsForTable($tableName)
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            $columns = [];
            foreach (['present_location_address_id', 'permanent_location_address_id'] as $column) {
                if (Schema::hasColumn($tableName, $column)) {
                    $columns[] = $column;
                }
            }

            if ($this->indexExists($tableName, $tableName . '_present_location_address_id_idx')) {
                $table->dropIndex($tableName . '_present_location_address_id_idx');
            }
            if ($this->indexExists($tableName, $tableName . '_permanent_location_address_id_idx')) {
                $table->dropIndex($tableName . '_permanent_location_address_id_idx');
            }

            if (count($columns)) {
                $table->dropColumn($columns);
            }
        });
    }

    private function canonicalAddressKey(array $parts)
    {
        $tokens = [
            $this->nullableTrim($parts['street'] ?? null),
            $this->nullableTrim($parts['barangay'] ?? null),
            $this->nullableTrim($parts['municipality'] ?? null),
            $this->nullableTrim($parts['province'] ?? null),
            $this->nullableTrim($parts['region'] ?? null),
            $this->nullableTrim($parts['zipcode'] ?? null),
        ];

        $joined = implode('|', array_map(function ($value) {
            return strtolower((string) $value);
        }, $tokens));

        $joined = trim($joined, '|');

        return $joined;
    }

    private function nullableTrim($value)
    {
        $text = trim((string) $value);
        return $text === '' ? null : $text;
    }

    private function foreignKeyExists($tableName, $foreignName)
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
            ->where('constraint_name', $foreignName)
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
