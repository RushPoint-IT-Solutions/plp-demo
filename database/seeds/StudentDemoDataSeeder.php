<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentDemoDataSeeder extends Seeder
{
    public function run()
    {
        $now = now();

        $studentId = DB::table('students')->updateOrInsert(
            ['student_no' => '2025-000001'],
            [
                'name' => 'Dela Cruz, Juan Miguel',
                'sex' => 'Male',
                'age' => 21,
                'college' => 'College of Computer Studies',
                'program' => 'BSIT',
                'curriculum' => 'BSIT 2022-2023',
                'year_level' => '4A',
                'scholarship' => 'FREE EDUCATION',
                'registration_no' => 'REG-2025-000001',
                'school_year' => '2025-2026',
                'semester' => '2nd Semester',
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        $student = DB::table('students')->where('student_no', '2025-000001')->first();
        if (!$student) {
            return;
        }

        DB::table('student_profiles')->updateOrInsert(
            ['student_no' => '2025-000001'],
            [
                'first_name' => 'Juan Miguel',
                'last_name' => 'Dela Cruz',
                'middle_name' => 'Santos',
                'suffix' => null,
                'nickname' => 'JM',
                'gender' => 'Male',
                'nationality' => 'Filipino',
                'religion' => 'Roman Catholic',
                'date_of_birth' => '2004-05-14',
                'place_of_birth' => 'Pasig City',
                'civil_status' => 'Single',
                'mobile_number' => '09171234567',
                'student_email' => 'student@plp.local',
                'present_street' => '12 Mabini Street',
                'present_barangay' => 'Kapasigan',
                'present_zipcode' => '1600',
                'present_municipality' => 'Pasig',
                'present_province' => 'Metro Manila',
                'present_region' => 'NCR',
                'permanent_street' => '12 Mabini Street',
                'permanent_barangay' => 'Kapasigan',
                'permanent_zipcode' => '1600',
                'permanent_municipality' => 'Pasig',
                'permanent_province' => 'Metro Manila',
                'permanent_region' => 'NCR',
                'same_as_present' => true,
                'is_orphan' => false,
                'is_first_gen' => true,
                'is_4ps' => false,
                'has_disability' => false,
                'is_foreign' => false,
                'mother_firstname' => 'Maria',
                'mother_middlename' => 'Reyes',
                'mother_lastname' => 'Dela Cruz',
                'mother_contact' => '09181234567',
                'mother_occupation' => 'Vendor',
                'mother_pensioner' => false,
                'father_firstname' => 'Jose',
                'father_middlename' => 'Castro',
                'father_lastname' => 'Dela Cruz',
                'father_contact' => '09191234567',
                'father_occupation' => 'Driver',
                'father_pensioner' => false,
                'guardian_firstname' => 'Ana',
                'guardian_middlename' => 'Luna',
                'guardian_lastname' => 'Dela Cruz',
                'guardian_contact' => '09201234567',
                'guardian_occupation' => 'Office Staff',
                'guardian_address' => '12 Mabini Street, Kapasigan, Pasig',
                'parent_marital_status' => 'Married',
                'monthly_family_income' => '20000-30000',
                'number_of_siblings' => 3,
                'household_members' => 6,
                'dependents' => 2,
                'junior_school' => 'Pasig City Science HS',
                'senior_school' => 'Pasig City Science SHS',
                'shs_track_strand' => 'STEM',
                'no_k12' => false,
                'lrn' => '123456789012',
                'family_income_source' => 'Employment',
                'living_situation' => 'With Parents',
                'working_student' => 'No',
                'has_scholarship' => 'Yes',
                'first_in_family_college' => 'Yes',
                'internet_access' => 'Yes',
                'it_tools_access' => 'Yes',
                'devices' => json_encode(['Laptop', 'Phone']),
                'lms_used' => 'Google Classroom',
                'lms_preferred' => 'Google Classroom',
                'lms_reasons' => json_encode(['Ease of use']),
                'preferred_class_time' => 'Morning',
                'evening_classes' => 'No',
                'profile_complete' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        DB::table('users')->updateOrInsert(
            ['username' => 'student'],
            [
                'name' => 'Student Demo User',
                'email' => 'student.demo@plp.local',
                'password' => Hash::make('student'),
                'module' => 'student',
                'student_id' => $student->id,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        $subjectIds = DB::table('subjects')->limit(4)->pluck('id');

        foreach ($subjectIds as $subjectId) {
            DB::table('student_subject')->updateOrInsert(
                ['student_id' => $student->id, 'subject_id' => $subjectId],
                ['student_id' => $student->id, 'subject_id' => $subjectId]
            );

            DB::table('student_subject_grades')->updateOrInsert(
                ['student_id' => $student->id, 'subject_id' => $subjectId],
                [
                    'prelim' => 1.75,
                    'midterm' => 1.50,
                    'final' => 1.50,
                    'final_average' => 1.58,
                    'remarks' => 'Passed',
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}
