<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ConvertAcademicCalendarEventTimeRangeToDateRange extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('academic_calendar_events')) {
            return;
        }

        if (!Schema::hasColumn('academic_calendar_events', 'date_from')) {
            Schema::table('academic_calendar_events', function (Blueprint $table) {
                $table->date('date_from')->nullable()->after('event_date');
                $table->date('date_to')->nullable()->after('date_from');
            });
        }

        if (Schema::hasColumn('academic_calendar_events', 'time_from')) {
            // Preserve intent: events that previously had a time range now span their event date.
            DB::table('academic_calendar_events')
                ->whereNotNull('time_from')
                ->orWhereNotNull('time_to')
                ->update([
                    'date_from' => DB::raw('event_date'),
                    'date_to' => DB::raw('event_date'),
                ]);

            Schema::table('academic_calendar_events', function (Blueprint $table) {
                $table->dropColumn(['time_from', 'time_to']);
            });
        }
    }

    public function down()
    {
        if (!Schema::hasTable('academic_calendar_events')) {
            return;
        }

        if (!Schema::hasColumn('academic_calendar_events', 'time_from')) {
            Schema::table('academic_calendar_events', function (Blueprint $table) {
                $table->time('time_from')->nullable()->after('event_date');
                $table->time('time_to')->nullable()->after('time_from');
            });
        }

        if (Schema::hasColumn('academic_calendar_events', 'date_from')) {
            Schema::table('academic_calendar_events', function (Blueprint $table) {
                $table->dropColumn(['date_from', 'date_to']);
            });
        }
    }
}
