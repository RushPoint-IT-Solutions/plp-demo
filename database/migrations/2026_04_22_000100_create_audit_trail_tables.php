<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuditTrailTables extends Migration
{
    public function up()
    {
        Schema::create('audit_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('event_code', 120);
            $table->unsignedBigInteger('actor_user_id')->nullable();
            $table->string('source_module', 120)->nullable();
            $table->string('source_action', 120)->nullable();
            $table->timestamps();

            $table->foreign('actor_user_id', 'audit_events_actor_fk')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->index(['event_code', 'created_at'], 'audit_events_code_created_idx');
            $table->index(['source_module', 'source_action'], 'audit_events_source_idx');
        });

        Schema::create('audit_event_subjects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('audit_event_id');
            $table->string('subject_type', 120);
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('subject_label', 255)->nullable();
            $table->timestamps();

            $table->foreign('audit_event_id', 'audit_subjects_event_fk')
                ->references('id')
                ->on('audit_events')
                ->onDelete('cascade');

            $table->index(['subject_type', 'subject_id'], 'audit_subjects_type_id_idx');
        });

        Schema::create('audit_event_changes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('audit_event_subject_id');
            $table->string('field_name', 120);
            $table->longText('old_value')->nullable();
            $table->longText('new_value')->nullable();
            $table->timestamps();

            $table->foreign('audit_event_subject_id', 'audit_changes_subject_fk')
                ->references('id')
                ->on('audit_event_subjects')
                ->onDelete('cascade');

            $table->index('field_name', 'audit_changes_field_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('audit_event_changes');
        Schema::dropIfExists('audit_event_subjects');
        Schema::dropIfExists('audit_events');
    }
}