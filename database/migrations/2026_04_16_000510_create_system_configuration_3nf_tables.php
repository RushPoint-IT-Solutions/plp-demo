<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSystemConfiguration3nfTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('system_config_signature_designations')) {
            Schema::create('system_config_signature_designations', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('code', 60)->unique();
                $table->string('name', 120)->unique();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('system_config_name_signatures')) {
            Schema::create('system_config_name_signatures', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('designation_id');
                $table->string('signer_name', 190);
                $table->string('signature_path', 255)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique('designation_id', 'scns_designation_unique');
                $table->index('is_active', 'scns_is_active_idx');
                $table->foreign('designation_id', 'scns_designation_fk')
                    ->references('id')
                    ->on('system_config_signature_designations')
                    ->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('system_cutoff_types')) {
            Schema::create('system_cutoff_types', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('code', 80)->unique();
                $table->string('name', 140)->unique();
                $table->string('description', 255)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('system_cutoff_entries')) {
            Schema::create('system_cutoff_entries', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('cutoff_type_id');
                $table->unsignedBigInteger('academic_term_id')->nullable();
                $table->string('student_no', 80)->nullable();
                $table->date('event_date')->nullable();
                $table->date('cutoff_date');
                $table->string('notes', 190)->nullable();
                $table->timestamps();

                $table->index(['cutoff_type_id', 'academic_term_id'], 'sce_type_term_idx');
                $table->index('student_no', 'sce_student_no_idx');
                $table->foreign('cutoff_type_id', 'sce_cutoff_type_fk')
                    ->references('id')
                    ->on('system_cutoff_types')
                    ->onDelete('cascade');
                $table->foreign('academic_term_id', 'sce_academic_term_fk')
                    ->references('id')
                    ->on('academic_terms')
                    ->onDelete('set null');
            });
        }

        if (!Schema::hasTable('system_curriculum_display_settings')) {
            Schema::create('system_curriculum_display_settings', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('academic_term_id');
                $table->string('display_status', 20);
                $table->timestamps();

                $table->unique('academic_term_id', 'scds_academic_term_unique');
                $table->foreign('academic_term_id', 'scds_academic_term_fk')
                    ->references('id')
                    ->on('academic_terms')
                    ->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('system_report_detail_settings')) {
            Schema::create('system_report_detail_settings', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('region', 190);
                $table->string('division', 190);
                $table->string('school_id', 120);
                $table->string('school_name', 190);
                $table->string('contact_details', 255);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index('is_active', 'srds_is_active_idx');
            });
        }

        if (!Schema::hasTable('system_email_sender_settings')) {
            Schema::create('system_email_sender_settings', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('sender_email', 190);
                $table->text('sender_password_encrypted');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index('is_active', 'sess_is_active_idx');
            });
        }

        if (!Schema::hasTable('system_inc_process_runs')) {
            Schema::create('system_inc_process_runs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('academic_term_id')->nullable();
                $table->unsignedBigInteger('triggered_by_user_id')->nullable();
                $table->unsignedInteger('processed_count')->default(0);
                $table->string('notes', 190)->nullable();
                $table->timestamps();

                $table->index(['academic_term_id', 'created_at'], 'sipr_term_created_idx');
                $table->foreign('academic_term_id', 'sipr_academic_term_fk')
                    ->references('id')
                    ->on('academic_terms')
                    ->onDelete('set null');
                $table->foreign('triggered_by_user_id', 'sipr_triggered_by_fk')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            });
        }

        $this->seedSignatureDesignations();
        $this->seedCutoffTypes();
        $this->seedReportDetails();
    }

    public function down()
    {
        if (Schema::hasTable('system_inc_process_runs')) {
            Schema::table('system_inc_process_runs', function (Blueprint $table) {
                $table->dropForeign('sipr_academic_term_fk');
                $table->dropForeign('sipr_triggered_by_fk');
            });
            Schema::dropIfExists('system_inc_process_runs');
        }

        Schema::dropIfExists('system_email_sender_settings');
        Schema::dropIfExists('system_report_detail_settings');

        if (Schema::hasTable('system_curriculum_display_settings')) {
            Schema::table('system_curriculum_display_settings', function (Blueprint $table) {
                $table->dropForeign('scds_academic_term_fk');
            });
            Schema::dropIfExists('system_curriculum_display_settings');
        }

        if (Schema::hasTable('system_cutoff_entries')) {
            Schema::table('system_cutoff_entries', function (Blueprint $table) {
                $table->dropForeign('sce_cutoff_type_fk');
                $table->dropForeign('sce_academic_term_fk');
            });
            Schema::dropIfExists('system_cutoff_entries');
        }

        Schema::dropIfExists('system_cutoff_types');

        if (Schema::hasTable('system_config_name_signatures')) {
            Schema::table('system_config_name_signatures', function (Blueprint $table) {
                $table->dropForeign('scns_designation_fk');
            });
            Schema::dropIfExists('system_config_name_signatures');
        }

        Schema::dropIfExists('system_config_signature_designations');
    }

    private function seedSignatureDesignations(): void
    {
        if (!Schema::hasTable('system_config_signature_designations')) {
            return;
        }

        $now = now();
        $rows = [
            ['code' => 'REGISTRAR', 'name' => 'University Registrar', 'sort_order' => 10, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'ACCOUNTING_HEAD', 'name' => 'Accounting Head', 'sort_order' => 20, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'ASSISTANT_REGISTRAR', 'name' => 'Assistant Registrar', 'sort_order' => 30, 'created_at' => $now, 'updated_at' => $now],
        ];

        foreach ($rows as $row) {
            $exists = DB::table('system_config_signature_designations')
                ->where('code', $row['code'])
                ->exists();

            if (!$exists) {
                DB::table('system_config_signature_designations')->insert($row);
            }
        }
    }

    private function seedCutoffTypes(): void
    {
        if (!Schema::hasTable('system_cutoff_types')) {
            return;
        }

        $now = now();
        $rows = [
            [
                'code' => 'ENROLLMENT',
                'name' => 'Enrollment',
                'description' => 'Enrollment cut-off settings',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'FACULTY_LOADING',
                'name' => 'Faculty Loading',
                'description' => 'Faculty loading cut-off settings',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'SECTION_OFFERING',
                'name' => 'Section Offering',
                'description' => 'Section offering cut-off settings',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'CHANGING_DELETING_ADDING',
                'name' => 'Changing/Deleting/Adding',
                'description' => 'Changing, deleting, and adding cut-off settings',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'CUT_OFF_REGISTRATION',
                'name' => 'Cut-off Registration',
                'description' => 'Per-student registration cut-off settings',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($rows as $row) {
            $exists = DB::table('system_cutoff_types')
                ->where('code', $row['code'])
                ->exists();

            if (!$exists) {
                DB::table('system_cutoff_types')->insert($row);
            }
        }
    }

    private function seedReportDetails(): void
    {
        if (!Schema::hasTable('system_report_detail_settings')) {
            return;
        }

        $hasRows = DB::table('system_report_detail_settings')->exists();
        if ($hasRows) {
            return;
        }

        DB::table('system_report_detail_settings')->insert([
            'region' => 'NCR',
            'division' => 'Pasig City',
            'school_id' => '000000',
            'school_name' => 'Pamantasan ng Lungsod ng Pasig',
            'contact_details' => 'registrar@plpasig.edu.ph',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
