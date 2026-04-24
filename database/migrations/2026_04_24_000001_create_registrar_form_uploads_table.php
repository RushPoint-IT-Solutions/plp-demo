<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegistrarFormUploadsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('registrar_form_uploads')) {
            return;
        }

        Schema::create('registrar_form_uploads', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('form_key', 120);
            $table->string('form_label', 190);
            $table->unsignedInteger('version_number')->default(1);
            $table->boolean('is_current')->default(true);
            $table->unsignedBigInteger('uploaded_by_user_id')->nullable();
            $table->string('original_filename', 190);
            $table->string('storage_disk', 40)->default('local');
            $table->string('storage_path', 255);
            $table->string('mime_type', 120)->nullable();
            $table->unsignedInteger('size_bytes')->default(0);
            $table->string('content_hash', 64)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['form_key', 'version_number']);
            $table->index(['form_key', 'is_current']);
            $table->index('uploaded_by_user_id');
            $table->index('content_hash');

            $table->foreign('uploaded_by_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('registrar_form_uploads');
    }
}