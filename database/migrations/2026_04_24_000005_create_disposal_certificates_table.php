<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDisposalCertificatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('disposal_certificates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('uuid', 36)->unique();
            $table->unsignedBigInteger('archive_id');
            $table->unsignedBigInteger('disposed_by');
            $table->enum('disposal_method', [
                'secure_delete',
                'anonymize',
                'export_then_delete'
            ]);
            $table->string('verification_hash', 64)->nullable();
            $table->string('certificate_number', 50)->unique();
            $table->text('disposal_details')->nullable();
            $table->timestamp('disposed_at')->useCurrent();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index('archive_id');
            $table->index('disposed_by');
            $table->index('certificate_number');
            
            $table->foreign('archive_id')->references('id')->on('archives')->onDelete('restrict');
            $table->foreign('disposed_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('disposal_certificates');
    }
}