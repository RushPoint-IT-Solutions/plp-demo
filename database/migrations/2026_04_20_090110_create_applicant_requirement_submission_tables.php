<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicantRequirementSubmissionTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('applicant_requirement_submissions')) {
            Schema::create('applicant_requirement_submissions', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('applicant_id');
                $table->unsignedBigInteger('registrar_requirement_policy_id');
                $table->boolean('is_submitted')->default(false);
                $table->string('remarks', 500)->nullable();
                $table->date('date_submitted')->nullable();
                $table->unsignedBigInteger('verified_by_user_id')->nullable();
                $table->timestamps();

                $table->unique(
                    ['applicant_id', 'registrar_requirement_policy_id'],
                    'ars_unique_applicant_policy'
                );

                $table->index('is_submitted', 'ars_submitted_idx');
                $table->index('date_submitted', 'ars_date_submitted_idx');

                $table->foreign('applicant_id', 'ars_applicant_fk')
                    ->references('id')
                    ->on('applicants')
                    ->onDelete('cascade');

                $table->foreign('registrar_requirement_policy_id', 'ars_policy_fk')
                    ->references('id')
                    ->on('registrar_requirement_policies')
                    ->onDelete('cascade');

                $table->foreign('verified_by_user_id', 'ars_verified_by_fk')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            });
        }

        if (!Schema::hasTable('applicant_requirement_submission_files')) {
            Schema::create('applicant_requirement_submission_files', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('applicant_requirement_submission_id');
                $table->unsignedBigInteger('uploaded_by_user_id')->nullable();
                $table->string('original_filename', 190);
                $table->string('storage_disk', 40)->default('public');
                $table->string('storage_path', 255);
                $table->string('mime_type', 120)->nullable();
                $table->unsignedInteger('size_bytes')->default(0);
                $table->timestamps();

                $table->unique('applicant_requirement_submission_id', 'arsf_submission_unique');
                $table->index('uploaded_by_user_id', 'arsf_uploaded_by_idx');
                $table->index('updated_at', 'arsf_updated_at_idx');

                $table->foreign('applicant_requirement_submission_id', 'arsf_submission_fk')
                    ->references('id')
                    ->on('applicant_requirement_submissions')
                    ->onDelete('cascade');

                $table->foreign('uploaded_by_user_id', 'arsf_uploaded_by_fk')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('applicant_requirement_submission_files')) {
            Schema::table('applicant_requirement_submission_files', function (Blueprint $table) {
                $table->dropForeign('arsf_submission_fk');
                $table->dropForeign('arsf_uploaded_by_fk');
                $table->dropUnique('arsf_submission_unique');
                $table->dropIndex('arsf_uploaded_by_idx');
                $table->dropIndex('arsf_updated_at_idx');
            });

            Schema::dropIfExists('applicant_requirement_submission_files');
        }

        if (Schema::hasTable('applicant_requirement_submissions')) {
            Schema::table('applicant_requirement_submissions', function (Blueprint $table) {
                $table->dropForeign('ars_applicant_fk');
                $table->dropForeign('ars_policy_fk');
                $table->dropForeign('ars_verified_by_fk');
                $table->dropUnique('ars_unique_applicant_policy');
                $table->dropIndex('ars_submitted_idx');
                $table->dropIndex('ars_date_submitted_idx');
            });

            Schema::dropIfExists('applicant_requirement_submissions');
        }
    }
}
