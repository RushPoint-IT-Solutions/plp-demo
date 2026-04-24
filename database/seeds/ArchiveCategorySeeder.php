<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ArchiveCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            [
                'name' => 'Academic Records',
                'description' => 'Grades, deficiencies, transcripts, and academic performance records',
                'retention_policy_id' => 1, // Student Grade Records
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Discipline Records',
                'description' => 'Student conduct, disciplinary actions, and behavioral records',
                'retention_policy_id' => 3, // Discipline Records
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Enrollment Records',
                'description' => 'Class rosters, section assignments, and enrollment data',
                'retention_policy_id' => 4, // Class Rosters
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Faculty Records',
                'description' => 'Faculty loads, evaluations, and employment records',
                'retention_policy_id' => 5, // Faculty Loads
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Administrative Records',
                'description' => 'Notifications, announcements, and general administrative documents',
                'retention_policy_id' => 6, // Slot Monitoring
                'is_active' => true,
                'sort_order' => 5,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Communication Records',
                'description' => 'Parent contacts, inquiries, and communication logs',
                'retention_policy_id' => 7, // Parent Contact Requests
                'is_active' => true,
                'sort_order' => 6,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Personnel Records',
                'description' => 'Faculty and staff employment and performance files',
                'retention_policy_id' => 9, // Faculty Files
                'is_active' => true,
                'sort_order' => 7,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('archive_categories')->insert($categories);
    }
}