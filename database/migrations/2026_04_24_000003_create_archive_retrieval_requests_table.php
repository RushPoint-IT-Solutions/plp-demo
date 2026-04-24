<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArchiveRetrievalRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archive_retrieval_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('uuid', 36)->unique();
            $table->unsignedBigInteger('archive_id');
            $table->unsignedBigInteger('requested_by');
            $table->text('request_reason');
            $table->enum('access_level', ['view', 'download', 'restore_temporary'])->default('view');
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'fulfilled',
                'expired',
                'cancelled'
            ])->default('pending');
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->index('requested_by');
            $table->index('status');
            $table->index('archive_id');
            
            $table->foreign('archive_id')->references('id')->on('archives')->onDelete('cascade');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('archive_retrieval_requests');
    }
}