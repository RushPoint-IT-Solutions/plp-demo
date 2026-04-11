<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSectionMergingOperationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('section_merging_operations')) {
            return;
        }

        Schema::create('section_merging_operations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('school_year', 20);
            $table->string('semester', 20);

            $table->unsignedBigInteger('source_slot_monitoring_id');
            $table->unsignedBigInteger('target_slot_monitoring_id');
            $table->unsignedBigInteger('source_course_id');
            $table->unsignedBigInteger('target_course_id');

            $table->string('source_section', 120);
            $table->string('target_section', 120);
            $table->string('source_subject', 150);
            $table->string('target_subject', 150);

            $table->unsignedSmallInteger('source_total_slots');
            $table->unsignedSmallInteger('source_enrolled_slots');
            $table->unsignedSmallInteger('target_total_slots_before');
            $table->unsignedSmallInteger('target_enrolled_slots_before');
            $table->unsignedSmallInteger('target_total_slots_after');
            $table->unsignedSmallInteger('target_enrolled_slots_after');

            $table->string('merge_status', 20)->default('completed');
            $table->unsignedBigInteger('merged_by_user_id')->nullable();
            $table->dateTime('merged_at')->nullable();
            $table->timestamps();

            $table->foreign('source_slot_monitoring_id', 'smo_source_slot_fk')
                ->references('id')
                ->on('slot_monitorings')
                ->onDelete('restrict');

            $table->foreign('target_slot_monitoring_id', 'smo_target_slot_fk')
                ->references('id')
                ->on('slot_monitorings')
                ->onDelete('restrict');

            $table->foreign('source_course_id', 'smo_source_course_fk')
                ->references('id')
                ->on('courses')
                ->onDelete('restrict');

            $table->foreign('target_course_id', 'smo_target_course_fk')
                ->references('id')
                ->on('courses')
                ->onDelete('restrict');

            $table->foreign('merged_by_user_id', 'smo_merged_by_user_fk')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->index(['school_year', 'semester', 'merge_status'], 'smo_filter_idx');
            $table->index(['source_slot_monitoring_id', 'target_slot_monitoring_id'], 'smo_pair_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('section_merging_operations')) {
            return;
        }

        Schema::table('section_merging_operations', function (Blueprint $table) {
            $table->dropForeign('smo_source_slot_fk');
            $table->dropForeign('smo_target_slot_fk');
            $table->dropForeign('smo_source_course_fk');
            $table->dropForeign('smo_target_course_fk');
            $table->dropForeign('smo_merged_by_user_fk');
            $table->dropIndex('smo_filter_idx');
            $table->dropIndex('smo_pair_idx');
        });

        Schema::dropIfExists('section_merging_operations');
    }
}
