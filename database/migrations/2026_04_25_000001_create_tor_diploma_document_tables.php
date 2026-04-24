<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTorDiplomaDocumentTables extends Migration
{
    public function up()
    {
        // 1NF lookup: document type (TOR, Diploma)
        if (!Schema::hasTable('tor_diploma_document_types')) {
            Schema::create('tor_diploma_document_types', function (Blueprint $table) {
                $table->increments('id');
                $table->string('code', 30)->unique();
                $table->string('name', 120);
                $table->timestamps();
            });

            \DB::table('tor_diploma_document_types')->insert([
                ['id' => 1, 'code' => 'tor', 'name' => 'Transcript of Records', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 2, 'code' => 'diploma', 'name' => 'Diploma', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // Main document tracking table (each generated/printed document)
        if (!Schema::hasTable('tor_diploma_documents')) {
            Schema::create('tor_diploma_documents', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('student_id');
                $table->unsignedInteger('document_type_id');
                $table->string('control_number', 60)->unique();
                $table->unsignedInteger('version_number')->default(1);
                $table->boolean('is_current')->default(true);
                $table->unsignedBigInteger('generated_by_user_id')->nullable();
                $table->text('footer_text')->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('generated_at')->nullable();
                $table->timestamps();

                $table->index(['student_id', 'document_type_id']);
                $table->index(['document_type_id', 'is_current']);
                $table->index('control_number');
                $table->index('generated_by_user_id');

                $table->foreign('student_id')
                    ->references('id')
                    ->on('students')
                    ->onDelete('cascade');

                $table->foreign('document_type_id')
                    ->references('id')
                    ->on('tor_diploma_document_types')
                    ->onDelete('restrict');

                $table->foreign('generated_by_user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            });
        }

        // Amendment history for issued documents
        if (!Schema::hasTable('tor_diploma_amendments')) {
            Schema::create('tor_diploma_amendments', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('tor_diploma_document_id');
                $table->unsignedBigInteger('amended_by_user_id')->nullable();
                $table->string('amendment_type', 80);
                $table->text('description')->nullable();
                $table->text('old_value')->nullable();
                $table->text('new_value')->nullable();
                $table->timestamps();

                $table->index('tor_diploma_document_id');
                $table->index('amended_by_user_id');

                $table->foreign('tor_diploma_document_id')
                    ->references('id')
                    ->on('tor_diploma_documents')
                    ->onDelete('cascade');

                $table->foreign('amended_by_user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            });
        }

        // Footer configuration table (global footer settings per document type)
        if (!Schema::hasTable('tor_diploma_footer_configs')) {
            Schema::create('tor_diploma_footer_configs', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('document_type_id');
                $table->string('key', 80);
                $table->text('value')->nullable();
                $table->unsignedBigInteger('updated_by_user_id')->nullable();
                $table->timestamps();

                $table->unique(['document_type_id', 'key']);

                $table->foreign('document_type_id')
                    ->references('id')
                    ->on('tor_diploma_document_types')
                    ->onDelete('cascade');

                $table->foreign('updated_by_user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            });

            // Seed default footer configs
            \DB::table('tor_diploma_footer_configs')->insert([
                ['document_type_id' => 1, 'key' => 'school_name', 'value' => 'PAMANTASAN NG LUNGSOD NG PASIG', 'updated_by_user_id' => null, 'created_at' => now(), 'updated_at' => now()],
                ['document_type_id' => 1, 'key' => 'school_address', 'value' => 'Alcalde Jose, Brgy. Kapasigan, Pasig City', 'updated_by_user_id' => null, 'created_at' => now(), 'updated_at' => now()],
                ['document_type_id' => 1, 'key' => 'disclaimer', 'value' => 'This is a computer-generated document. Any erasure or alteration is not valid without proper authorization from the Registrar.', 'updated_by_user_id' => null, 'created_at' => now(), 'updated_at' => now()],
                ['document_type_id' => 2, 'key' => 'school_name', 'value' => 'PAMANTASAN NG LUNGSOD NG PASIG', 'updated_by_user_id' => null, 'created_at' => now(), 'updated_at' => now()],
                ['document_type_id' => 2, 'key' => 'school_address', 'value' => 'Alcalde Jose, Brgy. Kapasigan, Pasig City', 'updated_by_user_id' => null, 'created_at' => now(), 'updated_at' => now()],
                ['document_type_id' => 2, 'key' => 'disclaimer', 'value' => 'This diploma is awarded under the authority of the Board of Regents of the University.', 'updated_by_user_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('tor_diploma_footer_configs');
        Schema::dropIfExists('tor_diploma_amendments');
        Schema::dropIfExists('tor_diploma_documents');
        Schema::dropIfExists('tor_diploma_document_types');
    }
}
