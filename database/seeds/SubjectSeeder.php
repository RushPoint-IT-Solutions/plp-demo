<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubjectSeeder extends Seeder
{
    public function run()
    {
        $subjects = [
            [
                'code'        => 'GEC19',
                'name'        => 'Life and Works of Rizal',
                'units'       => 2.0,
                'days'        => 'Sat',
                'time_start'  => '04:00PM',
                'time_end'    => '07:00PM',
                'room'        => 'RM 1',
                'faculty'     => 'Abejo, M.',
                'semester'    => '2nd Semester',
                'school_year' => '2025-2026',
            ],
            [
                'code'        => 'SAM125',
                'name'        => 'System Administration and Maintenance',
                'units'       => 3.0,
                'days'        => 'W,Th',
                'time_start'  => '01:00PM',
                'time_end'    => '02:00PM',
                'room'        => 'RM 5',
                'faculty'     => 'Abejo, M.',
                'semester'    => '2nd Semester',
                'school_year' => '2025-2026',
            ],
            [
                'code'        => 'CP126',
                'name'        => 'Capstone Project',
                'units'       => 3.0,
                'days'        => 'M,F',
                'time_start'  => '04:00PM',
                'time_end'    => '07:00PM',
                'room'        => 'RM 3',
                'faculty'     => 'Abejo, M.',
                'semester'    => '2nd Semester',
                'school_year' => '2025-2026',
            ],
        ];

        foreach ($subjects as $subject) {
            DB::table('subjects')->insert(array_merge($subject, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Enrol student id=1 into all subjects
        $subjectIds = DB::table('subjects')->pluck('id');
        foreach ($subjectIds as $subjectId) {
            DB::table('student_subject')->insert([
                'student_id' => 1,
                'subject_id' => $subjectId,
            ]);
        }
    }
}
