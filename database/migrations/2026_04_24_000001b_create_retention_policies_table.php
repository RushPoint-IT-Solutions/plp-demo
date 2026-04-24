<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRetentionPoliciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('retention_policies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 100)->unique();
            $table->text('description')->nullable();
            $table->string('record_type', 100);
            $table->integer('retention_period_months');
            $table->enum('archive_trigger', [
                'manual',
                'end_of_academic_year',
                'end_of_semester',
                'inactivity',
                'graduation',
                'separation'
            ])->default('end_of_academic_year');
            $table->integer('inactivity_months')->nullable();
            $table->enum('disposal_trigger', [
                'retention_expired',
                'manual_approval',
                'graduation_plus_years'
            ])->default('retention_expired');
            $table->integer('disposal_after_graduation_years')->nullable();
            $table->boolean('requires_approval')->default(true);
            $table->enum('approval_role', ['registrar', 'admin'])->default('admin');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique('record_type', 'uk_record_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('retention_policies');
    }
}