<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSampleStudentDocumentUploadRequirement extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('student_requirement_statuses')) {
            return;
        }

        Schema::table('student_requirement_statuses', function (Blueprint $table) {
            if (!Schema::hasColumn('student_requirement_statuses', 'uploaded_original_name')) {
                $table->string('uploaded_original_name', 190)->nullable()->after('remarks');
            }
            if (!Schema::hasColumn('student_requirement_statuses', 'uploaded_path')) {
                $table->string('uploaded_path', 255)->nullable()->after('uploaded_original_name');
            }
            if (!Schema::hasColumn('student_requirement_statuses', 'uploaded_mime')) {
                $table->string('uploaded_mime', 120)->nullable()->after('uploaded_path');
            }
            if (!Schema::hasColumn('student_requirement_statuses', 'uploaded_size')) {
                $table->unsignedBigInteger('uploaded_size')->nullable()->after('uploaded_mime');
            }
            if (!Schema::hasColumn('student_requirement_statuses', 'uploaded_at')) {
                $table->timestamp('uploaded_at')->nullable()->after('uploaded_size');
            }
        });
    }

    public function down()
    {
        if (Schema::hasTable('student_requirement_statuses')) {
            Schema::table('student_requirement_statuses', function (Blueprint $table) {
                foreach (['uploaded_at', 'uploaded_size', 'uploaded_mime', 'uploaded_path', 'uploaded_original_name'] as $column) {
                    if (Schema::hasColumn('student_requirement_statuses', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
}
