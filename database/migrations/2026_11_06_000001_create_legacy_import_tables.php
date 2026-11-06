<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateLegacyImportTables extends Migration
{
    public function up()
    {
        Schema::create('legacy_import_runs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->longText('manifest');
            $table->longText('summary');
            $table->timestamps();
        });
        Schema::create('legacy_employees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('legacy_id', 80)->unique();
            $table->string('employee_no', 80)->unique();
            $table->string('name', 255);
            $table->boolean('is_active');
            $table->boolean('is_teaching')->nullable();
            $table->unsignedBigInteger('faculty_id')->nullable();
            $table->foreign('faculty_id')->references('id')->on('faculties')->onDelete('set null');
            $table->timestamps();
        });
        Schema::create('legacy_import_sources', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('run_id');
            $table->foreign('run_id')->references('id')->on('legacy_import_runs');
            $table->string('kind', 20);
            $table->char('record_key', 64)->unique();
            $table->string('target_table', 60);
            $table->unsignedBigInteger('target_id');
            $table->string('source_file');
            $table->char('file_sha256', 64);
            $table->unsignedInteger('source_row');
            $table->longText('source_data');
            $table->timestamps();
        });
        Schema::create('legacy_import_locks', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
        });
        DB::table('legacy_import_locks')->insert(['id' => 1]);
    }

    public function down()
    {
        // Imported business records are deliberately retained on schema rollback.
        Schema::dropIfExists('legacy_import_sources');
        Schema::dropIfExists('legacy_employees');
        Schema::dropIfExists('legacy_import_runs');
        Schema::dropIfExists('legacy_import_locks');
    }
}
