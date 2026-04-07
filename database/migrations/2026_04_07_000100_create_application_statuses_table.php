<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateApplicationStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('application_statuses')) {
            Schema::create('application_statuses', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('status_code', 10)->unique();
                $table->string('status_name', 120)->unique();
                $table->text('status_message')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index('is_active');
            });
        }

        DB::table('application_statuses')->updateOrInsert(
            ['status_code' => 'D'],
            [
                'status_name' => 'Document Submitted',
                'status_message' => 'Document Submitted',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('application_statuses')->updateOrInsert(
            ['status_code' => 'O'],
            [
                'status_name' => 'On Probation',
                'status_message' => 'Process On Probation',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('application_statuses')->updateOrInsert(
            ['status_code' => 'P'],
            [
                'status_name' => 'In Process',
                'status_message' => 'Application, In Process',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('application_statuses')->updateOrInsert(
            ['status_code' => 'A'],
            [
                'status_name' => 'Accepted',
                'status_message' => 'Congratulations! We would like to inform you that your application is accepted! Thank You!',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('application_statuses')->updateOrInsert(
            ['status_code' => 'R'],
            [
                'status_name' => 'Rejected',
                'status_message' => 'Sorry, your application did not meet the Pamantasan ng Lungsod ng Pasig requirements.',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('application_statuses')->updateOrInsert(
            ['status_code' => 'I'],
            [
                'status_name' => 'Incomplete',
                'status_message' => 'Application Incomplete',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('application_statuses');
    }
}
