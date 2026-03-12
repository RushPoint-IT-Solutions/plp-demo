<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubjectSeeder extends Seeder
{
    public function run()
    {
        $subjects = [
            [
                'code'           => 'SAM125',
                'name'           => 'System Administration and Maintenance',
                'units'          => 3.0,
                'days'           => 'M,Th',
                'time_start'     => '01:00PM',
                'time_end'       => '02:00PM',
                'room'           => '5',
                'faculty'        => 'Abejo, M.',
                'year_section'   => '4-B',
                'course'         => 'BSCS',
                'grading_status' => 'Submitted',
                'semester'       => '2nd Semester',
                'school_year'    => '2025-2026',
            ],
            [
                'code'           => 'OOP111',
                'name'           => 'Object-Oriented Programming',
                'units'          => 3.0,
                'days'           => 'T,F',
                'time_start'     => '04:00PM',
                'time_end'       => '05:00PM',
                'room'           => '2',
                'faculty'        => 'Abejo, M.',
                'year_section'   => '4-B',
                'course'         => 'BSCS',
                'grading_status' => 'Open For Encoding',
                'semester'       => '2nd Semester',
                'school_year'    => '2025-2026',
            ],
            [
                'code'           => 'SPI128',
                'name'           => 'Social And Professional Issues',
                'units'          => 2.0,
                'days'           => 'W,Th',
                'time_start'     => '08:00AM',
                'time_end'       => '10:00AM',
                'room'           => '1',
                'faculty'        => 'Abejo, M.',
                'year_section'   => '4-A',
                'course'         => 'BSIT',
                'grading_status' => 'Open For Encoding',
                'semester'       => '2nd Semester',
                'school_year'    => '2025-2026',
            ],
            [
                'code'           => 'UTS12',
                'name'           => 'Understanding The Self',
                'units'          => 2.0,
                'days'           => 'F,Th',
                'time_start'     => '03:00PM',
                'time_end'       => '05:00PM',
                'room'           => '8',
                'faculty'        => 'Abejo, M.',
                'year_section'   => '1-C',
                'course'         => 'BSCS',
                'grading_status' => 'Open For Encoding',
                'semester'       => '2nd Semester',
                'school_year'    => '2025-2026',
            ],
        ];

        foreach ($subjects as $subject) {
            DB::table('subjects')->updateOrInsert(
                [
                    'code'        => $subject['code'],
                    'semester'    => $subject['semester'],
                    'school_year' => $subject['school_year'],
                ],
                array_merge($subject, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        // Enrol student id=1 into all subjects
        $subjectIds = DB::table('subjects')
            ->whereIn('code', array_column($subjects, 'code'))
            ->pluck('id');

        foreach ($subjectIds as $subjectId) {
            DB::table('student_subject')->updateOrInsert(
                ['student_id' => 1, 'subject_id' => $subjectId],
                ['student_id' => 1, 'subject_id' => $subjectId]
            );
        }
    }
}
