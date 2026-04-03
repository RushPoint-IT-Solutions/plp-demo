<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddApplicationProgressColumnsToApplicantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->string('application_status')->default('draft')->after('exam_result_status');
            $table->unsignedTinyInteger('application_draft_step')->default(1)->after('application_status');
            $table->dateTime('application_submitted_at')->nullable()->after('application_draft_step');
            $table->unsignedTinyInteger('application_portal_stage')->default(0)->after('application_submitted_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropColumn([
                'application_status',
                'application_draft_step',
                'application_submitted_at',
                'application_portal_stage',
            ]);
        });
    }
}
