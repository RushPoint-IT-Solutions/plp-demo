<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArchivesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archives', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('uuid', 36)->unique();
            $table->string('record_type', 100);
            $table->unsignedBigInteger('record_id');
            $table->string('original_table', 100);
            $table->unsignedBigInteger('archived_by');
            $table->timestamp('archived_at')->useCurrent();
            $table->enum('archive_category', ['academic', 'administrative', 'personnel', 'compliance']);
            $table->string('archive_reason', 255)->nullable();
            $table->unsignedBigInteger('retention_policy_id')->nullable();
            $table->timestamp('scheduled_disposal_at')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_restored')->default(false);
            $table->timestamp('restored_at')->nullable();
            $table->timestamp('restored_until')->nullable();
            
            $table->index('record_type');
            $table->index('archived_at');
            $table->index('archive_category');
            $table->index('scheduled_disposal_at');
            $table->index(['original_table', 'record_id']);
            
            $table->foreign('archived_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('retention_policy_id')->references('id')->on('retention_policies')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('archives');
    }
}