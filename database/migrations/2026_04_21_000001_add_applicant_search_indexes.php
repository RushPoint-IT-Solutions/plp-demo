<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApplicantSearchIndexes extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('applicants')) {
            return;
        }

        Schema::table('applicants', function (Blueprint $table) {
            $table->index('first_name', 'applicants_first_name_lookup_index');
            $table->index('middle_name', 'applicants_middle_name_lookup_index');
        });
    }

    public function down()
    {
        if (!Schema::hasTable('applicants')) {
            return;
        }

        Schema::table('applicants', function (Blueprint $table) {
            $table->dropIndex('applicants_middle_name_lookup_index');
            $table->dropIndex('applicants_first_name_lookup_index');
        });
    }
}
