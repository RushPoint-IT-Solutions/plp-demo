<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ArchiveSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $settings = [
            [
                'key' => 'auto_archive_enabled',
                'value' => 'true',
                'description' => 'Enable automatic archival based on retention policies',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'key' => 'auto_disposal_enabled',
                'value' => 'true',
                'description' => 'Enable automatic disposal of records past retention period',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'key' => 'disposal_warning_days',
                'value' => '30',
                'description' => 'Days before disposal to send warning notifications',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'key' => 'retrieval_request_expiry_days',
                'value' => '7',
                'description' => 'Days before retrieval request expires',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'key' => 'temp_restore_max_days',
                'value' => '30',
                'description' => 'Maximum days for temporary restoration',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'key' => 'secure_delete_iterations',
                'value' => '3',
                'description' => 'Number of overwrite passes for secure delete',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'key' => 'require_approval_for_disposal',
                'value' => 'true',
                'description' => 'Require approval before disposing records',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'key' => 'audit_log_retention_months',
                'value' => '84',
                'description' => 'How long to retain audit logs (7 years)',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('archive_settings')->insert($settings);
    }
}