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

        $events = [
            [
                'event_date' => '2026-03-28',
                'time_from' => null,
                'time_to' => null,
                'title' => 'Maundy Thursday',
                'venue' => 'National Holiday',
                'in_charge' => 'University Administration',
                'post_until' => '2026-03-28',
                'event_type' => 'holiday',
                'is_active' => true,
            ],
            [
                'event_date' => '2026-04-14',
                'time_from' => '08:00:00',
                'time_to' => '17:00:00',
                'title' => 'Midterm Examinations Start',
                'venue' => 'Main Campus',
                'in_charge' => 'Registrar Office',
                'post_until' => '2026-04-20',
                'event_type' => 'event',
                'is_active' => true,
            ],
            [
                'event_date' => '2026-06-12',
                'time_from' => null,
                'time_to' => null,
                'title' => 'Independence Day',
                'venue' => 'National Holiday',
                'in_charge' => 'University Administration',
                'post_until' => '2026-06-12',
                'event_type' => 'holiday',
                'is_active' => true,
            ],
        ];

        foreach ($events as $event) {
            DB::table('academic_calendar_events')->updateOrInsert(
                [
                    'event_date' => $event['event_date'],
                    'title' => $event['title'],
                ],
                array_merge($event, [
                    'updated_at' => $now,
                    'created_at' => $now,
                ])
            );
        }
    }
}
