<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NormalizeAcademicCalendarEventAudiencesTo3nf extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('academic_calendar_audience_types')) {
            Schema::create('academic_calendar_audience_types', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('code', 40)->unique();
                $table->string('label', 120);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('academic_calendar_event_audiences')) {
            Schema::create('academic_calendar_event_audiences', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('academic_calendar_event_id');
                $table->unsignedBigInteger('audience_type_id');
                $table->timestamps();

                $table->unique(['academic_calendar_event_id', 'audience_type_id'], 'acea_event_audience_unique');
                $table->index(['audience_type_id', 'academic_calendar_event_id'], 'acea_audience_event_idx');

                $table->foreign('academic_calendar_event_id', 'acea_event_fk')
                    ->references('id')
                    ->on('academic_calendar_events')
                    ->onDelete('cascade');

                $table->foreign('audience_type_id', 'acea_type_fk')
                    ->references('id')
                    ->on('academic_calendar_audience_types')
                    ->onDelete('cascade');
            });
        }

        $this->seedAudienceTypes();
        $this->backfillAudienceMappings();
        $this->addVisibilityIndex();
    }

    public function down()
    {
        $this->dropVisibilityIndex();

        if (Schema::hasTable('academic_calendar_event_audiences')) {
            Schema::dropIfExists('academic_calendar_event_audiences');
        }

        if (Schema::hasTable('academic_calendar_audience_types')) {
            Schema::dropIfExists('academic_calendar_audience_types');
        }
    }

    private function seedAudienceTypes()
    {
        if (!Schema::hasTable('academic_calendar_audience_types')) {
            return;
        }

        $now = now();
        $rows = [
            ['code' => 'student', 'label' => 'Student'],
            ['code' => 'faculty', 'label' => 'Faculty'],
            ['code' => 'applicant', 'label' => 'Applicant'],
        ];

        foreach ($rows as $row) {
            DB::table('academic_calendar_audience_types')->updateOrInsert(
                ['code' => $row['code']],
                [
                    'label' => $row['label'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }

    private function backfillAudienceMappings()
    {
        if (!Schema::hasTable('academic_calendar_events')
            || !Schema::hasTable('academic_calendar_audience_types')
            || !Schema::hasTable('academic_calendar_event_audiences')) {
            return;
        }

        $audienceTypeIds = DB::table('academic_calendar_audience_types')
            ->whereIn('code', ['student', 'faculty', 'applicant'])
            ->pluck('id', 'code')
            ->all();

        if (empty($audienceTypeIds)) {
            return;
        }

        $eventIds = DB::table('academic_calendar_events')->pluck('id');
        $now = now();

        foreach ($eventIds as $eventId) {
            foreach ($audienceTypeIds as $audienceTypeId) {
                DB::table('academic_calendar_event_audiences')->updateOrInsert(
                    [
                        'academic_calendar_event_id' => (int) $eventId,
                        'audience_type_id' => (int) $audienceTypeId,
                    ],
                    [
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }
        }
    }

    private function addVisibilityIndex()
    {
        if (!Schema::hasTable('academic_calendar_events')) {
            return;
        }

        if ($this->indexExists('academic_calendar_events', 'ace_active_visibility_idx')) {
            return;
        }

        Schema::table('academic_calendar_events', function (Blueprint $table) {
            $table->index(['is_active', 'post_until', 'event_date'], 'ace_active_visibility_idx');
        });
    }

    private function dropVisibilityIndex()
    {
        if (!Schema::hasTable('academic_calendar_events')) {
            return;
        }

        if (!$this->indexExists('academic_calendar_events', 'ace_active_visibility_idx')) {
            return;
        }

        Schema::table('academic_calendar_events', function (Blueprint $table) {
            $table->dropIndex('ace_active_visibility_idx');
        });
    }

    private function indexExists($tableName, $indexName)
    {
        if (DB::getDriverName() !== 'mysql') {
            return false;
        }

        $databaseName = DB::getDatabaseName();
        if (!$databaseName) {
            return false;
        }

        return DB::table('information_schema.statistics')
            ->where('table_schema', $databaseName)
            ->where('table_name', $tableName)
            ->where('index_name', $indexName)
            ->exists();
    }
}
