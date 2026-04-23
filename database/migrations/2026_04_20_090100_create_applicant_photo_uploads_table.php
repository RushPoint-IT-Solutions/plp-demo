<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicantPhotoUploadsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('applicant_photo_uploads')) {
            return;
        }

        Schema::create('applicant_photo_uploads', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('applicant_id');
            $table->unsignedBigInteger('uploaded_by_user_id')->nullable();
            $table->string('original_filename', 190);
            $table->string('storage_disk', 40)->default('public');
            $table->string('storage_path', 255);
            $table->string('mime_type', 120)->nullable();
            $table->unsignedInteger('size_bytes')->default(0);
            $table->timestamps();

            $table->unique('applicant_id', 'apu_applicant_unique');
            $table->index('uploaded_by_user_id', 'apu_uploaded_by_idx');
            $table->index('updated_at', 'apu_updated_at_idx');

            $table->foreign('applicant_id', 'apu_applicant_fk')
                ->references('id')
                ->on('applicants')
                ->onDelete('cascade');

            $table->foreign('uploaded_by_user_id', 'apu_uploaded_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        if (!Schema::hasTable('applicant_photo_uploads')) {
            return;
        }

        Schema::table('applicant_photo_uploads', function (Blueprint $table) {
            $table->dropForeign('apu_applicant_fk');
            $table->dropForeign('apu_uploaded_by_fk');
            $table->dropIndex('apu_uploaded_by_idx');
            $table->dropIndex('apu_updated_at_idx');
            $table->dropUnique('apu_applicant_unique');
        });

        Schema::dropIfExists('applicant_photo_uploads');
    }
}
