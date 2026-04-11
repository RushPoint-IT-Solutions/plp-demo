<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WaveBNormalizeStudentProfileLearningPreferences extends Migration
{
    public function up()
    {
        $this->createWaveBTables();
        $this->seedLookupDefaults();
        $this->backfillFromLegacyColumns();
        $this->dropLegacyColumns();
    }

    public function down()
    {
        $this->restoreLegacyColumns();
        $this->dropWaveBTables();
    }

    private function createWaveBTables()
    {
        if (!Schema::hasTable('student_profile_option_lookups')) {
            Schema::create('student_profile_option_lookups', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('domain', 80);
                $table->string('code', 120);
                $table->string('label', 190);
                $table->boolean('is_other')->default(false);
                $table->timestamps();

                $table->unique(['domain', 'code'], 'sp_option_lookup_domain_code_unique');
            });
        }

        if (!Schema::hasTable('student_profile_option_values')) {
            Schema::create('student_profile_option_values', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('student_profile_id');
                $table->string('domain', 80);
                $table->unsignedBigInteger('option_lookup_id')->nullable();
                $table->string('value_text', 255)->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();

                $table->index(['student_profile_id', 'domain'], 'sp_option_values_profile_domain_idx');
                $table->index('option_lookup_id', 'sp_option_values_lookup_idx');
            });
        }

        if (Schema::hasTable('student_profile_option_values')
            && !$this->foreignKeyExists('student_profile_option_values', 'sp_option_values_student_profile_id_foreign')) {
            Schema::table('student_profile_option_values', function (Blueprint $table) {
                $table->foreign('student_profile_id', 'sp_option_values_student_profile_id_foreign')
                    ->references('id')
                    ->on('student_profiles')
                    ->onDelete('cascade');
            });
        }

        if (Schema::hasTable('student_profile_option_values')
            && !$this->foreignKeyExists('student_profile_option_values', 'sp_option_values_lookup_id_foreign')) {
            Schema::table('student_profile_option_values', function (Blueprint $table) {
                $table->foreign('option_lookup_id', 'sp_option_values_lookup_id_foreign')
                    ->references('id')
                    ->on('student_profile_option_lookups')
                    ->onDelete('set null');
            });
        }
    }

    private function seedLookupDefaults()
    {
        $defaultOptions = [
            'internet_access' => [
                'Reliable Home Internet',
                'Mobile Data Only',
                'Shared/Wi-Fi Spot',
                'No Internet Access',
            ],
            'it_tools_access' => [
                'Personal Computer/Laptop',
                'Shared Computer',
                'Mobile Phone Only',
                'Limited Access',
            ],
            'devices' => [
                'Smartphone',
                'Tablet',
                'Laptop/Notebook Computer',
                'Desktop Computer',
                'Others',
            ],
            'lms_used' => [
                'Google Classroom',
                'Moodle',
                'Canvas',
                'MS Teams',
                'School LMS',
                'Others',
            ],
            'lms_preferred' => [
                'Google Classroom',
                'Moodle',
                'Canvas',
                'MS Teams',
                'School LMS',
                'Others',
            ],
            'lms_reasons' => [
                'Reliable',
                'User Friendly',
                'Better Communication Feature',
                'Accessible using mobile devices',
                'Fast Feedback on Assessments',
                'Others',
            ],
            'preferred_class_time' => [
                'Morning',
                'Afternoon',
                'Evening',
                'Flexible',
            ],
        ];

        foreach ($defaultOptions as $domain => $labels) {
            foreach ($labels as $label) {
                $this->ensureLookupOption($domain, $label, strcasecmp($label, 'Others') === 0);
            }
        }
    }

    private function backfillFromLegacyColumns()
    {
        if (!Schema::hasTable('student_profiles')) {
            return;
        }

        $requiredColumns = [
            'internet_access',
            'it_tools_access',
            'devices',
            'devices_other',
            'lms_used',
            'lms_used_other',
            'lms_preferred',
            'lms_preferred_other',
            'lms_reasons',
            'lms_reasons_other',
            'preferred_class_time',
        ];

        foreach ($requiredColumns as $column) {
            if (!Schema::hasColumn('student_profiles', $column)) {
                return;
            }
        }

        DB::table('student_profiles')
            ->select([
                'id',
                'internet_access',
                'it_tools_access',
                'devices',
                'devices_other',
                'lms_used',
                'lms_used_other',
                'lms_preferred',
                'lms_preferred_other',
                'lms_reasons',
                'lms_reasons_other',
                'preferred_class_time',
            ])
            ->orderBy('id')
            ->chunk(200, function ($rows) {
                foreach ($rows as $row) {
                    $profileId = (int) $row->id;

                    $this->syncDomainValues($profileId, 'internet_access', [$row->internet_access], null);
                    $this->syncDomainValues($profileId, 'it_tools_access', [$row->it_tools_access], null);

                    $devices = $this->parseJsonOrScalarList($row->devices);
                    $this->syncDomainValues($profileId, 'devices', $devices, $row->devices_other);

                    $this->syncDomainValues($profileId, 'lms_used', [$row->lms_used], $row->lms_used_other);
                    $this->syncDomainValues($profileId, 'lms_preferred', [$row->lms_preferred], $row->lms_preferred_other);

                    $reasons = $this->parseJsonOrScalarList($row->lms_reasons);
                    $this->syncDomainValues($profileId, 'lms_reasons', $reasons, $row->lms_reasons_other);

                    $this->syncDomainValues($profileId, 'preferred_class_time', [$row->preferred_class_time], null);
                }
            });
    }

    private function dropLegacyColumns()
    {
        if (!Schema::hasTable('student_profiles')) {
            return;
        }

        $legacyColumns = [
            'internet_access',
            'it_tools_access',
            'devices',
            'devices_other',
            'lms_used',
            'lms_used_other',
            'lms_preferred',
            'lms_preferred_other',
            'lms_reasons',
            'lms_reasons_other',
            'preferred_class_time',
        ];

        $dropColumns = [];
        foreach ($legacyColumns as $column) {
            if (Schema::hasColumn('student_profiles', $column)) {
                $dropColumns[] = $column;
            }
        }

        if (!count($dropColumns)) {
            return;
        }

        Schema::table('student_profiles', function (Blueprint $table) use ($dropColumns) {
            $table->dropColumn($dropColumns);
        });
    }

    private function restoreLegacyColumns()
    {
        if (!Schema::hasTable('student_profiles')) {
            return;
        }

        Schema::table('student_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('student_profiles', 'internet_access')) {
                $table->text('internet_access')->nullable()->after('first_in_family_college');
            }
            if (!Schema::hasColumn('student_profiles', 'it_tools_access')) {
                $table->text('it_tools_access')->nullable()->after('internet_access');
            }
            if (!Schema::hasColumn('student_profiles', 'devices')) {
                $table->text('devices')->nullable()->after('it_tools_access');
            }
            if (!Schema::hasColumn('student_profiles', 'devices_other')) {
                $table->string('devices_other', 255)->nullable()->after('devices');
            }
            if (!Schema::hasColumn('student_profiles', 'lms_used')) {
                $table->text('lms_used')->nullable()->after('devices_other');
            }
            if (!Schema::hasColumn('student_profiles', 'lms_used_other')) {
                $table->string('lms_used_other', 255)->nullable()->after('lms_used');
            }
            if (!Schema::hasColumn('student_profiles', 'lms_preferred')) {
                $table->text('lms_preferred')->nullable()->after('lms_used_other');
            }
            if (!Schema::hasColumn('student_profiles', 'lms_preferred_other')) {
                $table->string('lms_preferred_other', 255)->nullable()->after('lms_preferred');
            }
            if (!Schema::hasColumn('student_profiles', 'lms_reasons')) {
                $table->text('lms_reasons')->nullable()->after('lms_preferred_other');
            }
            if (!Schema::hasColumn('student_profiles', 'lms_reasons_other')) {
                $table->string('lms_reasons_other', 255)->nullable()->after('lms_reasons');
            }
            if (!Schema::hasColumn('student_profiles', 'preferred_class_time')) {
                $table->text('preferred_class_time')->nullable()->after('lms_reasons_other');
            }
        });
    }

    private function dropWaveBTables()
    {
        if (Schema::hasTable('student_profile_option_values')) {
            if ($this->foreignKeyExists('student_profile_option_values', 'sp_option_values_student_profile_id_foreign')) {
                Schema::table('student_profile_option_values', function (Blueprint $table) {
                    $table->dropForeign('sp_option_values_student_profile_id_foreign');
                });
            }

            if ($this->foreignKeyExists('student_profile_option_values', 'sp_option_values_lookup_id_foreign')) {
                Schema::table('student_profile_option_values', function (Blueprint $table) {
                    $table->dropForeign('sp_option_values_lookup_id_foreign');
                });
            }

            Schema::drop('student_profile_option_values');
        }

        if (Schema::hasTable('student_profile_option_lookups')) {
            Schema::drop('student_profile_option_lookups');
        }
    }

    private function parseJsonOrScalarList($rawValue)
    {
        if ($rawValue === null) {
            return [];
        }

        $stringValue = trim((string) $rawValue);
        if ($stringValue === '') {
            return [];
        }

        $decoded = json_decode($stringValue, true);
        if (is_array($decoded)) {
            $values = [];
            foreach ($decoded as $item) {
                $itemText = trim((string) $item);
                if ($itemText !== '') {
                    $values[] = $itemText;
                }
            }
            return $values;
        }

        return [$stringValue];
    }

    private function syncDomainValues($studentProfileId, $domain, array $values, $otherText)
    {
        DB::table('student_profile_option_values')
            ->where('student_profile_id', $studentProfileId)
            ->where('domain', $domain)
            ->delete();

        $sequence = 0;
        foreach ($values as $value) {
            $label = trim((string) $value);
            if ($label === '') {
                continue;
            }

            $isOtherOption = strcasecmp($label, 'Others') === 0;
            $lookupId = $this->ensureLookupOption($domain, $label, $isOtherOption);
            $resolvedOtherText = $isOtherOption ? trim((string) $otherText) : '';

            DB::table('student_profile_option_values')->insert([
                'student_profile_id' => $studentProfileId,
                'domain' => $domain,
                'option_lookup_id' => $lookupId,
                'value_text' => $resolvedOtherText !== '' ? $resolvedOtherText : null,
                'sort_order' => $sequence,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $sequence++;
        }
    }

    private function ensureLookupOption($domain, $label, $isOther)
    {
        $normalizedCode = $this->normalizeCode($label);

        $existing = DB::table('student_profile_option_lookups')
            ->where('domain', $domain)
            ->where('code', $normalizedCode)
            ->first();

        if ($existing) {
            return (int) $existing->id;
        }

        DB::table('student_profile_option_lookups')->insert([
            'domain' => $domain,
            'code' => $normalizedCode,
            'label' => $label,
            'is_other' => (bool) $isOther,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return (int) DB::getPdo()->lastInsertId();
    }

    private function normalizeCode($value)
    {
        $text = strtolower(trim((string) $value));
        $text = preg_replace('/[^a-z0-9]+/', '_', $text);
        $text = trim((string) $text, '_');

        return $text !== '' ? $text : 'value';
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
