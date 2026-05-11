<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInterviewMedicalFieldsToApplicants extends Migration
{
    public function up()
    {
        Schema::table('applicants', function (Blueprint $table) {
            if (!Schema::hasColumn('applicants', 'interview_date')) {
                $table->dateTime('interview_date')->nullable()->after('exam_score');
            }

            if (!Schema::hasColumn('applicants', 'interview_room')) {
                $table->string('interview_room', 190)->nullable()->after('interview_date');
            }

            if (!Schema::hasColumn('applicants', 'interview_status')) {
                $table->string('interview_status', 40)->nullable()->after('interview_room');
            }

            if (!Schema::hasColumn('applicants', 'medical_clearance_status')) {
                $table->string('medical_clearance_status', 40)->nullable()->after('interview_status');
            }
        });
    }

    public function down()
    {
        Schema::table('applicants', function (Blueprint $table) {
            foreach ([
                'medical_clearance_status',
                'interview_status',
                'interview_room',
                'interview_date',
            ] as $column) {
                if (Schema::hasColumn('applicants', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
