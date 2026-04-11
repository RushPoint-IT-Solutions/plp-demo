<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class StudentDemoDataSeeder extends Seeder
{
    public function run()
    {
        $now = now();

        // ── students table: schema-aware ───────────────────────────────────────
        $hasProgramColumn       = Schema::hasColumn('students', 'program');
        $hasCourseIdColumn      = Schema::hasColumn('students', 'course_id');
        $hasYearLevelColumn     = Schema::hasColumn('students', 'year_level');
        $hasYearBlockIdColumn   = Schema::hasColumn('students', 'year_block_id');
        $hasSchoolYearColumn    = Schema::hasColumn('students', 'school_year');
        $hasSemesterColumn      = Schema::hasColumn('students', 'semester');
        $hasAcademicTermIdColumn = Schema::hasColumn('students', 'academic_term_id');

        $courseId       = null;
        $yearBlockId    = null;
        $academicTermId = null;

        if ($hasCourseIdColumn && Schema::hasTable('courses')) {
            $courseId = DB::table('courses')->where('code', 'BSIT')->value('id');
        }

        if ($hasYearBlockIdColumn && Schema::hasTable('year_blocks')) {
            $yearBlockId = DB::table('year_blocks')->where('label', '4A')->value('id');
            if (!$yearBlockId) {
                $yearBlockId = DB::table('year_blocks')->orderBy('id')->value('id');
            }
        }

        if ($hasAcademicTermIdColumn && Schema::hasTable('academic_terms')) {
            $academicTermId = DB::table('academic_terms')
                ->whereRaw("LOWER(TRIM(school_year)) = '2025-2026'")
                ->whereRaw("LOWER(TRIM(term)) IN ('second', 'second semester', '2nd semester')")
                ->value('id');
            if (!$academicTermId) {
                $academicTermId = DB::table('academic_terms')->orderByDesc('school_year')->value('id');
            }
        }

        $studentPayload = [
            'name'            => 'Dela Cruz, Juan Miguel',
            'sex'             => 'Male',
            'age'             => 21,
            'college'         => 'College of Computer Studies',
            'curriculum'      => 'BSIT 2022-2023',
            'scholarship'     => 'FREE EDUCATION',
            'registration_no' => 'REG-2025-000001',
            'updated_at'      => $now,
            'created_at'      => $now,
        ];

        if ($hasProgramColumn)        { $studentPayload['program']         = 'BSIT'; }
        if ($hasCourseIdColumn)        { $studentPayload['course_id']       = $courseId; }
        if ($hasYearLevelColumn)       { $studentPayload['year_level']      = '4A'; }
        if ($hasYearBlockIdColumn)     { $studentPayload['year_block_id']   = $yearBlockId; }
        if ($hasSchoolYearColumn)      { $studentPayload['school_year']     = '2025-2026'; }
        if ($hasSemesterColumn)        { $studentPayload['semester']        = '2nd Semester'; }
        if ($hasAcademicTermIdColumn)  { $studentPayload['academic_term_id'] = $academicTermId; }

        DB::table('students')->updateOrInsert(
            ['student_no' => '2025-000001'],
            $studentPayload
        );

        $student = DB::table('students')->where('student_no', '2025-000001')->first();
        if (!$student) {
            return;
        }

        // ── student_profiles table: schema-aware ───────────────────────────────
        // Wave B (2026_04_08_000280) removes the learning-preference text columns
        // and replaces them with student_profile_option_values. Guard every field.
        $profileColumns = Schema::getColumnListing('student_profiles');
        $hasCol = fn($col) => in_array($col, $profileColumns, true);

        $profilePayload = [
            'first_name'            => 'Juan Miguel',
            'last_name'             => 'Dela Cruz',
            'middle_name'           => 'Santos',
            'suffix'                => null,
            'nickname'              => 'JM',
            'gender'                => 'Male',
            'nationality'           => 'Filipino',
            'religion'              => 'Roman Catholic',
            'date_of_birth'         => '2004-05-14',
            'place_of_birth'        => 'Pasig City',
            'civil_status'          => 'Single',
            'mobile_number'         => '09171234567',
            'student_email'         => 'student@plp.local',
            'present_street'        => '12 Mabini Street',
            'present_barangay'      => 'Kapasigan',
            'present_zipcode'       => '1600',
            'present_municipality'  => 'Pasig',
            'present_province'      => 'Metro Manila',
            'present_region'        => 'NCR',
            'permanent_street'      => '12 Mabini Street',
            'permanent_barangay'    => 'Kapasigan',
            'permanent_zipcode'     => '1600',
            'permanent_municipality'=> 'Pasig',
            'permanent_province'    => 'Metro Manila',
            'permanent_region'      => 'NCR',
            'same_as_present'       => true,
            'is_orphan'             => false,
            'is_first_gen'          => true,
            'is_4ps'                => false,
            'has_disability'        => false,
            'is_foreign'            => false,
            'mother_firstname'      => 'Maria',
            'mother_middlename'     => 'Reyes',
            'mother_lastname'       => 'Dela Cruz',
            'mother_contact'        => '09181234567',
            'mother_occupation'     => 'Vendor',
            'mother_pensioner'      => false,
            'father_firstname'      => 'Jose',
            'father_middlename'     => 'Castro',
            'father_lastname'       => 'Dela Cruz',
            'father_contact'        => '09191234567',
            'father_occupation'     => 'Driver',
            'father_pensioner'      => false,
            'guardian_firstname'    => 'Ana',
            'guardian_middlename'   => 'Luna',
            'guardian_lastname'     => 'Dela Cruz',
            'guardian_contact'      => '09201234567',
            'guardian_occupation'   => 'Office Staff',
            'guardian_address'      => '12 Mabini Street, Kapasigan, Pasig',
            'parent_marital_status' => 'Married',
            'monthly_family_income' => '20000-30000',
            'number_of_siblings'    => 3,
            'household_members'     => 6,
            'dependents'            => 2,
            'junior_school'         => 'Pasig City Science HS',
            'senior_school'         => 'Pasig City Science SHS',
            'shs_track_strand'      => 'STEM',
            'no_k12'                => false,
            'lrn'                   => '123456789012',
            'family_income_source'  => 'Employment',
            'living_situation'      => 'With Parents',
            'working_student'       => false,
            'has_scholarship'       => true,
            'first_in_family_college' => true,
            'profile_complete'      => true,
            'updated_at'            => $now,
            'created_at'            => $now,
        ];

        // Legacy learning-preference columns (dropped by Wave B)
        if ($hasCol('internet_access'))      { $profilePayload['internet_access']     = 'Yes'; }
        if ($hasCol('it_tools_access'))      { $profilePayload['it_tools_access']     = 'Yes'; }
        if ($hasCol('devices'))              { $profilePayload['devices']             = json_encode(['Laptop', 'Phone']); }
        if ($hasCol('lms_used'))             { $profilePayload['lms_used']            = 'Google Classroom'; }
        if ($hasCol('lms_preferred'))        { $profilePayload['lms_preferred']       = 'Google Classroom'; }
        if ($hasCol('lms_reasons'))          { $profilePayload['lms_reasons']         = json_encode(['Ease of use']); }
        if ($hasCol('preferred_class_time')) { $profilePayload['preferred_class_time'] = 'Morning'; }
        if ($hasCol('evening_classes'))      { $profilePayload['evening_classes']     = false; }

        DB::table('student_profiles')->updateOrInsert(
            ['student_no' => '2025-000001'],
            $profilePayload
        );

        // ── If Wave B tables exist, seed the normalized preference values ──────
        if (Schema::hasTable('student_profile_option_values')
            && Schema::hasTable('student_profile_option_lookups')) {

            $profileId = DB::table('student_profiles')
                ->where('student_no', '2025-000001')
                ->value('id');

            if ($profileId) {
                $this->seedProfileOptionValue($profileId, 'internet_access', 'Yes');
                $this->seedProfileOptionValue($profileId, 'it_tools_access', 'Yes');
                $this->seedProfileOptionValue($profileId, 'devices', 'Laptop/Notebook Computer');
                $this->seedProfileOptionValue($profileId, 'devices', 'Mobile Phone Only');
                $this->seedProfileOptionValue($profileId, 'lms_used', 'Google Classroom');
                $this->seedProfileOptionValue($profileId, 'lms_preferred', 'Google Classroom');
                $this->seedProfileOptionValue($profileId, 'lms_reasons', 'User Friendly');
                $this->seedProfileOptionValue($profileId, 'preferred_class_time', 'Morning');
            }
        }

        // ── users ─────────────────────────────────────────────────────────────
        DB::table('users')->updateOrInsert(
            ['username' => 'student'],
            [
                'name'                 => 'Student Demo User',
                'email'                => 'student.demo@plp.local',
                'password'             => Hash::make('student'),
                'module'               => 'student',
                'force_password_reset' => false,
                'student_id'           => $student->id,
                'updated_at'           => $now,
                'created_at'           => $now,
            ]
        );

        // ── enrol student in subjects ─────────────────────────────────────────
        $subjectIds = DB::table('subjects')->limit(4)->pluck('id');

        foreach ($subjectIds as $subjectId) {
            DB::table('student_subject')->updateOrInsert(
                ['student_id' => $student->id, 'subject_id' => $subjectId],
                ['student_id' => $student->id, 'subject_id' => $subjectId]
            );

            DB::table('student_subject_grades')->updateOrInsert(
                ['student_id' => $student->id, 'subject_id' => $subjectId],
                [
                    'prelim'        => 1.75,
                    'midterm'       => 1.50,
                    'final'         => 1.50,
                    'final_average' => 1.58,
                    'remarks'       => 'Passed',
                    'updated_at'    => $now,
                    'created_at'    => $now,
                ]
            );
        }
    }

    /**
     * Upsert a single preference row into the Wave-B normalized tables.
     * Silently skips if the lookup cannot be resolved.
     */
    private function seedProfileOptionValue($profileId, $domain, $label)
    {
        $code = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '_', $label), '_'));
        if ($code === '') {
            return;
        }

        $lookupId = DB::table('student_profile_option_lookups')
            ->where('domain', $domain)
            ->where('code', $code)
            ->value('id');

        if (!$lookupId) {
            // Insert the lookup on-the-fly (mirrors ensureLookupOption in Wave B)
            DB::table('student_profile_option_lookups')->updateOrInsert(
                ['domain' => $domain, 'code' => $code],
                [
                    'label'      => $label,
                    'is_other'   => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            $lookupId = DB::table('student_profile_option_lookups')
                ->where('domain', $domain)
                ->where('code', $code)
                ->value('id');
        }

        if (!$lookupId) {
            return;
        }

        $exists = DB::table('student_profile_option_values')
            ->where('student_profile_id', $profileId)
            ->where('domain', $domain)
            ->where('option_lookup_id', $lookupId)
            ->exists();

        if (!$exists) {
            DB::table('student_profile_option_values')->insert([
                'student_profile_id' => $profileId,
                'domain'             => $domain,
                'option_lookup_id'   => $lookupId,
                'value_text'         => null,
                'sort_order'         => 0,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }
    }
}
