<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArchiveDisposalQueueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archive_disposal_queue', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('uuid', 36)->unique();
            $table->unsignedBigInteger('archive_id');
            $table->timestamp('scheduled_date');
            $table->enum('status', [
                'pending_approval',
                'approved',
                'rejected',
                'disposed',
                'cancelled'
            ])->default('pending_approval');
            $table->unsignedBigInteger('requested_by');
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->unsignedBigInteger('disposed_by')->nullable();
            $table->timestamp('disposed_at')->nullable();
            $table->enum('disposal_method', [
                'secure_delete',
                'anonymize',
                'export_then_delete'
            ])->default('secure_delete');
            $table->string('disposal_certificate_number', 50)->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('scheduled_date');
            $table->index('archive_id');
            
            $table->foreign('archive_id')->references('id')->on('archives')->onDelete('cascade');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('disposed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('archive_disposal_queue');
    }
}