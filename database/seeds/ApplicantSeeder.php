<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApplicantSeeder extends Seeder
{
    public function run()
    {
        DB::table('applicants')->updateOrInsert(
            ['applicant_id' => '2526B0177'],
            [
                'applicant_id'        => '2526B0177',
                'lrn'                 => '123456789012',
                'last_name'           => 'Austero',
                'first_name'          => 'Andrea Jane',
                'middle_name'         => 'Santos',
                'suffix'              => null,
                'nickname'            => 'AJ',
                'gender'              => 'Female',
                'nationality'         => 'Filipino',
                'religion'            => 'Roman Catholic',
                'date_of_birth'       => '2004-05-15',
                'place_of_birth'      => 'Pasig City',
                'age'                 => 21,
                'civil_status'        => 'Single',
                'mobile_number'       => '09171234567',
                'email_address'       => 'andrea.austero@email.com',
                'photo'               => null,
                // Present Address
                'present_street'      => '123 Rizal St.',
                'present_barangay'    => 'Bagong Ilog',
                'present_zipcode'     => '1600',
                'present_municipality'=> 'Pasig City',
                'present_province'    => 'Metro Manila',
                'present_region'      => 'NCR',
                // Permanent Address
                'same_as_present'     => true,
                'permanent_street'    => '123 Rizal St.',
                'permanent_barangay'  => 'Bagong Ilog',
                'permanent_zipcode'   => '1600',
                'permanent_municipality' => 'Pasig City',
                'permanent_province'  => 'Metro Manila',
                'permanent_region'    => 'NCR',
                // Exam
                'exam_date'           => '2026-04-10 08:00:00',
                'exam_room'           => 'Room 201 - Main Building',
                'exam_result_status'  => 'Pending',
                'exam_score'          => null,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]
        );
    }
}
