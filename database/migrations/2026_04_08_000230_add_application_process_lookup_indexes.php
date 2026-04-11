<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApplicationProcessLookupIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->index(['last_name', 'first_name', 'middle_name'], 'applicants_name_lookup_index');
            $table->index('created_at', 'applicants_created_at_lookup_index');
            $table->index('updated_at', 'applicants_updated_at_lookup_index');
        });

        Schema::table('applicant_application_preferences', function (Blueprint $table) {
            $table->index(['apply_course_id', 'applicant_id'], 'aap_course_applicant_lookup_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('applicant_application_preferences', function (Blueprint $table) {
            $table->dropIndex('aap_course_applicant_lookup_index');
        });

        Schema::table('applicants', function (Blueprint $table) {
            $table->dropIndex('applicants_name_lookup_index');
            $table->dropIndex('applicants_created_at_lookup_index');
            $table->dropIndex('applicants_updated_at_lookup_index');
        });
    }
}
