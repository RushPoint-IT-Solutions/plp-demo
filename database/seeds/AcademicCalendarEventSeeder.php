<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AcademicCalendarEventSeeder extends Seeder
{
    public function run()
    {
        if (!Schema::hasTable('academic_calendar_events')) {
            return;
        }

        $now = now();
        $hasEventTypeColumn = Schema::hasColumn('academic_calendar_events', 'event_type');
        $hasEventTypeIdColumn = Schema::hasColumn('academic_calendar_events', 'event_type_id');
        $hasAudienceTypesTable = Schema::hasTable('academic_calendar_audience_types');
        $hasEventAudienceTable = Schema::hasTable('academic_calendar_event_audiences');

        $eventTypeIds = [];
        if ($hasEventTypeIdColumn && Schema::hasTable('academic_event_types')) {
            foreach (['holiday', 'event'] as $code) {
                DB::table('academic_event_types')->updateOrInsert(
                    ['code' => $code],
                    ['label' => ucfirst($code), 'created_at' => $now, 'updated_at' => $now]
                );
            }

            $eventTypeIds = DB::table('academic_event_types')->pluck('id', 'code')->toArray();
        }

        $audienceTypeIds = [];
        if ($hasAudienceTypesTable) {
            foreach (['student', 'faculty', 'applicant'] as $code) {
                DB::table('academic_calendar_audience_types')->updateOrInsert(
                    ['code' => $code],
                    ['label' => ucfirst($code), 'created_at' => $now, 'updated_at' => $now]
                );
            }

            $audienceTypeIds = DB::table('academic_calendar_audience_types')
                ->whereIn('code', ['student', 'faculty', 'applicant'])
                ->pluck('id', 'code')
                ->toArray();
        }

        $events = [
            [
                'event_date' => '2026-03-28',
                'date_from' => '2026-03-28',
                'date_to' => '2026-03-28',
                'title' => 'Maundy Thursday',
                'venue' => 'National Holiday',
                'in_charge' => 'University Administration',
                'post_until' => '2026-03-28',
                'event_type' => 'holiday',
                'is_active' => true,
            ],
            [
                'event_date' => '2026-04-14',
                'date_from' => '2026-04-14',
                'date_to' => '2026-04-20',
                'title' => 'Midterm Examinations Start',
                'venue' => 'Main Campus',
                'in_charge' => 'Registrar Office',
                'post_until' => '2026-04-20',
                'event_type' => 'event',
                'is_active' => true,
            ],
            [
                'event_date' => '2026-06-12',
                'date_from' => '2026-06-12',
                'date_to' => '2026-06-12',
                'title' => 'Independence Day',
                'venue' => 'National Holiday',
                'in_charge' => 'University Administration',
                'post_until' => '2026-06-12',
                'event_type' => 'holiday',
                'is_active' => true,
            ],
        ];

        foreach ($events as $event) {
            $payload = [
                'event_date' => $event['event_date'],
                'date_from' => $event['date_from'],
                'date_to' => $event['date_to'],
                'title' => $event['title'],
                'venue' => $event['venue'],
                'in_charge' => $event['in_charge'],
                'post_until' => $event['post_until'],
                'is_active' => $event['is_active'],
            ];

            if ($hasEventTypeColumn) {
                $payload['event_type'] = $event['event_type'];
            }

            if ($hasEventTypeIdColumn) {
                $payload['event_type_id'] = $eventTypeIds[$event['event_type']] ?? null;
            }

            DB::table('academic_calendar_events')->updateOrInsert(
                [
                    'event_date' => $event['event_date'],
                    'title' => $event['title'],
                ],
                array_merge($payload, [
                    'updated_at' => $now,
                    'created_at' => $now,
                ])
            );

            if ($hasEventAudienceTable && !empty($audienceTypeIds)) {
                $eventId = DB::table('academic_calendar_events')
                    ->where('event_date', $event['event_date'])
                    ->where('title', $event['title'])
                    ->value('id');

                if ($eventId) {
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
        }
    }
}
