<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateParentContactRequest3nfTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('parent_contact_request_topics')) {
            Schema::create('parent_contact_request_topics', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('code', 40)->unique();
                $table->string('name', 120)->unique();
                $table->unsignedSmallInteger('sort_order')->default(10);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['is_active', 'sort_order'], 'pcrt_active_sort_idx');
            });
        }

        if (!Schema::hasTable('parent_contact_request_statuses')) {
            Schema::create('parent_contact_request_statuses', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('code', 40)->unique();
                $table->string('name', 120)->unique();
                $table->unsignedSmallInteger('sort_order')->default(10);
                $table->boolean('is_terminal')->default(false);
                $table->timestamps();

                $table->index(['is_terminal', 'sort_order'], 'pcrs_terminal_sort_idx');
            });
        }

        if (!Schema::hasTable('parent_contact_request_channels')) {
            Schema::create('parent_contact_request_channels', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('code', 40)->unique();
                $table->string('name', 80)->unique();
                $table->unsignedSmallInteger('sort_order')->default(10);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['is_active', 'sort_order'], 'pcrc_active_sort_idx');
            });
        }

        if (!Schema::hasTable('parent_contact_requests')) {
            Schema::create('parent_contact_requests', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('reference_no', 40)->unique();
                $table->unsignedBigInteger('parent_id');
                $table->unsignedBigInteger('student_id')->nullable();
                $table->unsignedBigInteger('topic_id');
                $table->unsignedBigInteger('status_id');
                $table->unsignedBigInteger('channel_id');
                $table->unsignedBigInteger('submitted_by_user_id')->nullable();
                $table->unsignedBigInteger('resolved_by_user_id')->nullable();
                $table->string('subject', 190);
                $table->text('message');
                $table->string('contact_email_snapshot', 190)->nullable();
                $table->string('contact_mobile_snapshot', 40)->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->text('resolution_notes')->nullable();
                $table->timestamps();

                $table->index(['parent_id', 'created_at'], 'pcr_parent_created_idx');
                $table->index(['status_id', 'created_at'], 'pcr_status_created_idx');
                $table->index('student_id', 'pcr_student_idx');
                $table->index('topic_id', 'pcr_topic_idx');
                $table->index('channel_id', 'pcr_channel_idx');

                $table->foreign('parent_id', 'pcr_parent_fk')
                    ->references('id')
                    ->on('parents')
                    ->onDelete('cascade');

                $table->foreign('student_id', 'pcr_student_fk')
                    ->references('id')
                    ->on('students')
                    ->onDelete('set null');

                $table->foreign('topic_id', 'pcr_topic_fk')
                    ->references('id')
                    ->on('parent_contact_request_topics')
                    ->onDelete('restrict');

                $table->foreign('status_id', 'pcr_status_fk')
                    ->references('id')
                    ->on('parent_contact_request_statuses')
                    ->onDelete('restrict');

                $table->foreign('channel_id', 'pcr_channel_fk')
                    ->references('id')
                    ->on('parent_contact_request_channels')
                    ->onDelete('restrict');

                $table->foreign('submitted_by_user_id', 'pcr_submitted_by_fk')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');

                $table->foreign('resolved_by_user_id', 'pcr_resolved_by_fk')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            });
        }

        $this->seedTopics();
        $this->seedStatuses();
        $this->seedChannels();
    }

    public function down()
    {
        Schema::dropIfExists('parent_contact_requests');
        Schema::dropIfExists('parent_contact_request_channels');
        Schema::dropIfExists('parent_contact_request_statuses');
        Schema::dropIfExists('parent_contact_request_topics');
    }

    private function seedTopics()
    {
        if (!Schema::hasTable('parent_contact_request_topics')) {
            return;
        }

        $now = now();
        $rows = [
            ['code' => 'GRADES_RECORDS', 'name' => 'Grades and Records', 'sort_order' => 10, 'is_active' => true],
            ['code' => 'ACCOUNT_ACCESS', 'name' => 'Account Access', 'sort_order' => 20, 'is_active' => true],
            ['code' => 'ENROLLMENT_SERVICES', 'name' => 'Enrollment and Services', 'sort_order' => 30, 'is_active' => true],
            ['code' => 'TECHNICAL_ISSUE', 'name' => 'Technical Issue', 'sort_order' => 40, 'is_active' => true],
            ['code' => 'OTHER', 'name' => 'Other Concern', 'sort_order' => 50, 'is_active' => true],
        ];

        foreach ($rows as $row) {
            DB::table('parent_contact_request_topics')->updateOrInsert(
                ['code' => $row['code']],
                [
                    'name' => $row['name'],
                    'sort_order' => $row['sort_order'],
                    'is_active' => $row['is_active'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }

    private function seedStatuses()
    {
        if (!Schema::hasTable('parent_contact_request_statuses')) {
            return;
        }

        $now = now();
        $rows = [
            ['code' => 'NEW', 'name' => 'New', 'sort_order' => 10, 'is_terminal' => false],
            ['code' => 'IN_REVIEW', 'name' => 'In Review', 'sort_order' => 20, 'is_terminal' => false],
            ['code' => 'RESOLVED', 'name' => 'Resolved', 'sort_order' => 30, 'is_terminal' => true],
            ['code' => 'CLOSED', 'name' => 'Closed', 'sort_order' => 40, 'is_terminal' => true],
        ];

        foreach ($rows as $row) {
            DB::table('parent_contact_request_statuses')->updateOrInsert(
                ['code' => $row['code']],
                [
                    'name' => $row['name'],
                    'sort_order' => $row['sort_order'],
                    'is_terminal' => $row['is_terminal'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }

    private function seedChannels()
    {
        if (!Schema::hasTable('parent_contact_request_channels')) {
            return;
        }

        $now = now();
        $rows = [
            ['code' => 'IN_APP', 'name' => 'In-App Response', 'sort_order' => 10, 'is_active' => true],
            ['code' => 'EMAIL', 'name' => 'Email', 'sort_order' => 20, 'is_active' => true],
            ['code' => 'PHONE', 'name' => 'Phone Call', 'sort_order' => 30, 'is_active' => true],
        ];

        foreach ($rows as $row) {
            DB::table('parent_contact_request_channels')->updateOrInsert(
                ['code' => $row['code']],
                [
                    'name' => $row['name'],
                    'sort_order' => $row['sort_order'],
                    'is_active' => $row['is_active'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}
