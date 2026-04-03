<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseCatalogSeeder extends Seeder
{
    public function run()
    {
        if (app()->environment('production')) {
            throw new \Exception('Cannot seed in production.');
        }

        $now = now();

        $departments = [
            ['code' => 'CS', 'description' => 'College of Computer Studies'],
            ['code' => 'BUS', 'description' => 'College of Business Administration'],
            ['code' => 'EDU', 'description' => 'College of Education'],
        ];

        foreach ($departments as $department) {
            DB::table('departments')->updateOrInsert(
                ['code' => $department['code']],
                [
                    'description' => $department['description'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $departmentIds = DB::table('departments')
            ->whereIn('code', array_column($departments, 'code'))
            ->pluck('id', 'code');

        $deanDirectorId = DB::table('faculties')->where('code', 'FAC-001')->value('id');

        $courses = [
            [
                'code' => 'BSCS',
                'name' => 'Bachelor of Science in Computer Science',
                'program_type' => 'college',
                'department_code' => 'CS',
                'description' => 'Core computing and software development program.',
                'slots' => 320,
                'track_category' => null,
            ],
            [
                'code' => 'BSIT',
                'name' => 'Bachelor of Science in Information Technology',
                'program_type' => 'college',
                'department_code' => 'CS',
                'description' => 'Applied information technology and systems operations.',
                'slots' => 360,
                'track_category' => null,
            ],
            [
                'code' => 'BSEMC',
                'name' => 'Bachelor of Science in Entertainment and Multimedia Computing',
                'program_type' => 'college',
                'department_code' => 'CS',
                'description' => 'Game, media, and interactive computing program.',
                'slots' => 220,
                'track_category' => null,
            ],
            [
                'code' => 'BSBA',
                'name' => 'Bachelor of Science in Business Administration',
                'program_type' => 'college',
                'department_code' => 'BUS',
                'description' => 'Business operations and management fundamentals.',
                'slots' => 280,
                'track_category' => null,
            ],
            [
                'code' => 'BSED',
                'name' => 'Bachelor of Secondary Education',
                'program_type' => 'college',
                'department_code' => 'EDU',
                'description' => 'Teacher education with discipline specialization.',
                'slots' => 210,
                'track_category' => null,
            ],
            [
                'code' => 'SHS-STEM',
                'name' => 'Senior High School - Science, Technology, Engineering and Mathematics',
                'program_type' => 'senior_high',
                'department_code' => 'EDU',
                'description' => 'Senior high school strand for STEM.',
                'slots' => 250,
                'track_category' => 'STEM',
            ],
            [
                'code' => 'SHS-ABM',
                'name' => 'Senior High School - Accountancy, Business and Management',
                'program_type' => 'senior_high',
                'department_code' => 'BUS',
                'description' => 'Senior high school strand for ABM.',
                'slots' => 240,
                'track_category' => 'ABM',
            ],
            [
                'code' => 'SHS-HUMSS',
                'name' => 'Senior High School - Humanities and Social Sciences',
                'program_type' => 'senior_high',
                'department_code' => 'EDU',
                'description' => 'Senior high school strand for HUMSS.',
                'slots' => 210,
                'track_category' => 'HUMSS',
            ],
            [
                'code' => 'SHS-GAS',
                'name' => 'Senior High School - General Academic Strand',
                'program_type' => 'senior_high',
                'department_code' => 'EDU',
                'description' => 'Senior high school strand for GAS.',
                'slots' => 180,
                'track_category' => 'GAS',
            ],
            [
                'code' => 'SHS-ICT',
                'name' => 'Senior High School - Information and Communications Technology',
                'program_type' => 'senior_high',
                'department_code' => 'CS',
                'description' => 'Senior high school strand for ICT.',
                'slots' => 220,
                'track_category' => 'ICT',
            ],
        ];

        foreach ($courses as $course) {
            $departmentId = $departmentIds->get($course['department_code']);
            if (!$departmentId) {
                continue;
            }

            DB::table('courses')->updateOrInsert(
                ['code' => $course['code']],
                [
                    'name' => $course['name'],
                    'program_type' => $course['program_type'],
                    'department_id' => $departmentId,
                    'description' => $course['description'],
                    'slots' => $course['slots'],
                    'track_category' => $course['track_category'],
                    'non_filipino' => false,
                    'dean_director_id' => $deanDirectorId,
                    'program_file' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
