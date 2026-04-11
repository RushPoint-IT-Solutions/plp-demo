<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class ApplicantBulkSeeder extends Seeder
{
    public function run()
    {
        if (app()->environment('production')) {
            throw new \Exception('Cannot seed in production.');
        }

        $now = now();

        $firstNames = [
            'Andrea', 'Mark', 'Paolo', 'Trisha', 'Caleb', 'Lea', 'Nico', 'Jessa', 'Rina', 'Dale',
            'Miguel', 'Anna', 'Joshua', 'Aira', 'Franco', 'Mika', 'John', 'Patricia', 'Kurt', 'Elaine',
            'Noel', 'Shane', 'Ralph', 'Bianca', 'Kevin', 'Louie', 'Janine', 'Carlo', 'Eunice', 'Troy',
        ];

        $lastNames = [
            'Austero', 'Bares', 'Mendoza', 'Domingo', 'Ramirez', 'Javier', 'Flores', 'Aquino', 'Salazar', 'Gamboa',
            'Reyes', 'Dela Cruz', 'Villanueva', 'Santos', 'Garcia', 'Cruz', 'Torres', 'Lopez', 'Castro', 'Navarro',
            'Bautista', 'Fernandez', 'Ramos', 'Herrera', 'Nolasco', 'Lim', 'Molina', 'Valencia', 'Soriano', 'Manalo',
        ];

        $streetBases = [
            'Rizal', 'Mabini', 'Burgos', 'Bonifacio', 'A. Mabini', 'Ortigas', 'Caruncho', 'Sumulong', 'Shaw', 'C. Raymundo',
        ];

        $barangays = [
            'Bagong Ilog', 'Kapitolyo', 'Pineda', 'San Antonio', 'Oranbo', 'Maybunga', 'Rosario', 'Pinagbuhatan', 'Caniogan', 'Ugong',
        ];

        $municipalities = [
            'Pasig City', 'Taguig City', 'Makati City', 'Mandaluyong City', 'Quezon City',
        ];

        $courseIds = DB::table('courses')
            ->where('program_type', 'college')
            ->orderBy('id')
            ->pluck('id')
            ->values()
            ->all();

        $strands = DB::table('courses')
            ->whereNotNull('track_category')
            ->where('track_category', '!=', '')
            ->orderBy('track_category')
            ->pluck('track_category')
            ->unique()
            ->values()
            ->all();

        if (empty($strands)) {
            $strands = ['STEM', 'ABM', 'HUMSS', 'GAS', 'ICT'];
        }

        $hasApplicationStatusColumn = Schema::hasColumn('applicants', 'application_status');
        $hasApplicationStatusIdColumn = Schema::hasColumn('applicants', 'application_status_id');
        $hasExamResultStatusColumn = Schema::hasColumn('applicants', 'exam_result_status');
        $hasExamResultStatusIdColumn = Schema::hasColumn('applicants', 'exam_result_status_id');

        $applicationStatusIds = [];
        if ($hasApplicationStatusIdColumn && Schema::hasTable('applicant_application_statuses')) {
            foreach (['draft', 'submitted'] as $code) {
                DB::table('applicant_application_statuses')->updateOrInsert(
                    ['code' => $code],
                    ['label' => $code, 'created_at' => $now, 'updated_at' => $now]
                );
            }

            $applicationStatusIds = DB::table('applicant_application_statuses')->pluck('id', 'code')->toArray();
        }

        $examResultStatusIds = [];
        if ($hasExamResultStatusIdColumn && Schema::hasTable('applicant_exam_result_statuses')) {
            foreach (['Pending', 'Passed', 'Failed'] as $code) {
                DB::table('applicant_exam_result_statuses')->updateOrInsert(
                    ['code' => $code],
                    ['label' => $code, 'created_at' => $now, 'updated_at' => $now]
                );
            }

            $examResultStatusIds = DB::table('applicant_exam_result_statuses')->pluck('id', 'code')->toArray();
        }

        $hasYearLevelColumn = Schema::hasColumn('applicant_application_preferences', 'year_level');
        $hasYearLevelIdColumn = Schema::hasColumn('applicant_application_preferences', 'year_level_id');
        $hasSemesterColumn = Schema::hasColumn('applicant_application_preferences', 'semester');
        $hasSchoolYearColumn = Schema::hasColumn('applicant_application_preferences', 'school_year');
        $hasAcademicTermIdColumn = Schema::hasColumn('applicant_application_preferences', 'academic_term_id');

        $yearLevelIds = [];
        if ($hasYearLevelIdColumn && Schema::hasTable('applicant_year_levels')) {
            foreach (['1st Year', 'Grade 11'] as $label) {
                DB::table('applicant_year_levels')->updateOrInsert(
                    ['code' => $label],
                    ['label' => $label, 'created_at' => $now, 'updated_at' => $now]
                );
            }

            $yearLevelIds = DB::table('applicant_year_levels')->pluck('id', 'code')->toArray();
        }

        $preferenceSchoolYear = '2026-2027';
        $preferenceTermLabel = 'First Semester';
        $academicTermId = null;
        if ($hasAcademicTermIdColumn && Schema::hasTable('academic_terms')) {
            $academicTermId = DB::table('academic_terms')
                ->where('school_year', $preferenceSchoolYear)
                ->whereRaw("LOWER(TRIM(term)) IN ('first', 'first semester')")
                ->value('id');
        }

        for ($i = 0; $i < 30; $i++) {
            $sequence = 2001 + $i;
            $applicantCode = '2526B' . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);

            $firstName = $firstNames[$i % count($firstNames)];
            $lastName = $lastNames[$i % count($lastNames)];
            $middleName = chr(65 + ($i % 26)) . '.';

            $presentStreet = (string) (($i + 8) * 3) . ' ' . $streetBases[$i % count($streetBases)] . ' St.';
            $presentBarangay = $barangays[$i % count($barangays)];
            $presentMunicipality = $municipalities[$i % count($municipalities)];
            $presentProvince = ($presentMunicipality === 'Quezon City') ? 'Metro Manila' : 'Metro Manila';

            $isSubmitted = ($i % 3 !== 0);
            $applicationStatus = $isSubmitted ? 'submitted' : 'draft';
            $draftStep = $isSubmitted ? 4 : (($i % 4) + 1);
            $portalStage = $isSubmitted ? ($i % 2) : 0;

            $examDate = null;
            if ($isSubmitted) {
                $examDate = Carbon::now()->copy()->addDays(4 + $i)->setTime(8 + ($i % 4), 0, 0);
            }

            $examResultStatus = 'Pending';
            if ($isSubmitted && $i % 10 === 0) {
                $examResultStatus = 'Passed';
            } elseif ($isSubmitted && $i % 13 === 0) {
                $examResultStatus = 'Failed';
            }

            $examScore = null;
            if ($examResultStatus === 'Passed') {
                $examScore = 82 + ($i % 13);
            }
            if ($examResultStatus === 'Failed') {
                $examScore = 58 + ($i % 10);
            }

            $applicantPayload = [
                'lrn' => str_pad((string) (120000000000 + $i), 12, '0', STR_PAD_LEFT),
                'last_name' => $lastName,
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'suffix' => null,
                'nickname' => $firstName,
                'gender' => ($i % 2 === 0) ? 'Female' : 'Male',
                'nationality' => 'Filipino',
                'religion' => ($i % 2 === 0) ? 'Roman Catholic' : 'Born Again Christian',
                'date_of_birth' => Carbon::now()->copy()->subYears(18 + ($i % 6))->subDays($i + 30)->toDateString(),
                'place_of_birth' => $presentMunicipality,
                'age' => 18 + ($i % 6),
                'civil_status' => 'Single',
                'mobile_number' => '09' . str_pad((string) (171000000 + $i), 9, '0', STR_PAD_LEFT),
                'email_address' => 'applicant.' . strtolower($applicantCode) . '@plp.local',
                'photo' => null,
                'present_street' => $presentStreet,
                'present_barangay' => $presentBarangay,
                'present_zipcode' => (string) (1600 + ($i % 20)),
                'present_municipality' => $presentMunicipality,
                'present_province' => $presentProvince,
                'present_region' => 'NCR',
                'same_as_present' => true,
                'permanent_street' => $presentStreet,
                'permanent_barangay' => $presentBarangay,
                'permanent_zipcode' => (string) (1600 + ($i % 20)),
                'permanent_municipality' => $presentMunicipality,
                'permanent_province' => $presentProvince,
                'permanent_region' => 'NCR',
                'exam_date' => $examDate,
                'exam_room' => $isSubmitted ? ('Room ' . (200 + ($i % 20)) . ' - Main Building') : null,
                'exam_score' => $examScore,
                'application_draft_step' => $draftStep,
                'application_submitted_at' => $isSubmitted ? Carbon::now()->copy()->subDays($i % 12) : null,
                'application_portal_stage' => $portalStage,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if ($hasExamResultStatusColumn) {
                $applicantPayload['exam_result_status'] = $examResultStatus;
            }

            if ($hasApplicationStatusColumn) {
                $applicantPayload['application_status'] = $applicationStatus;
            }

            if ($hasExamResultStatusIdColumn) {
                $applicantPayload['exam_result_status_id'] = $examResultStatusIds[$examResultStatus] ?? null;
            }

            if ($hasApplicationStatusIdColumn) {
                $applicantPayload['application_status_id'] = $applicationStatusIds[$applicationStatus] ?? null;
            }

            DB::table('applicants')->updateOrInsert(
                ['applicant_id' => $applicantCode],
                $applicantPayload
            );

            $applicantId = DB::table('applicants')->where('applicant_id', $applicantCode)->value('id');
            if (!$applicantId) {
                continue;
            }

            DB::table('users')->updateOrInsert(
                ['username' => $applicantCode],
                [
                    'name' => trim($firstName . ' ' . $lastName),
                    'email' => 'user.' . strtolower($applicantCode) . '@plp.local',
                    'password' => Hash::make('PLP-' . $applicantCode),
                    'module' => 'applicant',
                    'force_password_reset' => false,
                    'student_id' => null,
                    'faculty_id' => null,
                    'registrar_id' => null,
                    'applicant_id' => $applicantId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            DB::table('applicant_educational_backgrounds')->updateOrInsert(
                ['applicant_id' => $applicantId],
                [
                    'junior_school' => 'Pasig Integrated School',
                    'senior_school' => 'PLP Senior High School',
                    'shs_track_strand' => $strands[$i % count($strands)],
                    'no_k12' => false,
                    'learner_reference_number' => str_pad((string) (120000000000 + $i), 12, '0', STR_PAD_LEFT),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            DB::table('applicant_family_backgrounds')->updateOrInsert(
                ['applicant_id' => $applicantId],
                [
                    'mother_last_name' => $lastName,
                    'mother_first_name' => 'Maria',
                    'mother_middle_name' => 'L.',
                    'mother_nationality' => 'Filipino',
                    'mother_religion' => 'Roman Catholic',
                    'mother_date_of_birth' => Carbon::now()->copy()->subYears(44 + ($i % 10))->toDateString(),
                    'mother_mobile_number' => '09' . str_pad((string) (181000000 + $i), 9, '0', STR_PAD_LEFT),
                    'mother_occupation' => 'Office Staff',
                    'mother_company_address' => 'Ortigas Center, Pasig City',
                    'mother_estimated_monthly_income' => '25000',
                    'mother_residence_address' => $presentStreet . ', ' . $presentBarangay,
                    'mother_email_address' => 'mother.' . strtolower($applicantCode) . '@plp.local',
                    'father_last_name' => $lastName,
                    'father_first_name' => 'Jose',
                    'father_middle_name' => 'R.',
                    'father_nationality' => 'Filipino',
                    'father_religion' => 'Roman Catholic',
                    'father_date_of_birth' => Carbon::now()->copy()->subYears(46 + ($i % 10))->toDateString(),
                    'father_mobile_number' => '09' . str_pad((string) (191000000 + $i), 9, '0', STR_PAD_LEFT),
                    'father_occupation' => 'Technician',
                    'father_company_address' => 'Makati City',
                    'father_estimated_monthly_income' => '28000',
                    'father_residence_address' => $presentStreet . ', ' . $presentBarangay,
                    'father_email_address' => 'father.' . strtolower($applicantCode) . '@plp.local',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $applyProgram = ($i % 2 === 0) ? 'college' : 'senior_high';
            $applyCourseId = null;
            $applyStrand = null;

            if ($applyProgram === 'college' && !empty($courseIds)) {
                $applyCourseId = $courseIds[$i % count($courseIds)];
            }

            if ($applyProgram === 'senior_high') {
                $applyStrand = $strands[$i % count($strands)];
            }

            $preferencePayload = [
                'apply_program' => $applyProgram,
                'apply_strand' => $applyStrand,
                'apply_course_id' => $applyCourseId,
                'entry_classification' => 'Regular Freshman',
                'application_date' => Carbon::now()->copy()->subDays($i % 40)->toDateString(),
                'campus' => 'Pasig',
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if ($hasYearLevelColumn) {
                $preferencePayload['year_level'] = ($applyProgram === 'college') ? '1st Year' : 'Grade 11';
            }

            if ($hasYearLevelIdColumn) {
                $preferencePayload['year_level_id'] = $yearLevelIds[($applyProgram === 'college') ? '1st Year' : 'Grade 11'] ?? null;
            }

            if ($hasSemesterColumn) {
                $preferencePayload['semester'] = $preferenceTermLabel;
            }

            if ($hasSchoolYearColumn) {
                $preferencePayload['school_year'] = $preferenceSchoolYear;
            }

            if ($hasAcademicTermIdColumn) {
                $preferencePayload['academic_term_id'] = $academicTermId;
            }

            DB::table('applicant_application_preferences')->updateOrInsert(
                ['applicant_id' => $applicantId],
                $preferencePayload
            );
        }

        $genericApplicantId = DB::table('applicants')->where('applicant_id', '2526B0177')->value('id');
        if (!$genericApplicantId) {
            $genericApplicantId = DB::table('applicants')->orderBy('id')->value('id');
        }

        if ($genericApplicantId) {
            DB::table('users')->updateOrInsert(
                ['username' => 'applicant'],
                [
                    'name' => 'Applicant Demo User',
                    'email' => 'applicant@plp.local',
                    'password' => Hash::make('applicant'),
                    'module' => 'applicant',
                    'force_password_reset' => false,
                    'student_id' => null,
                    'faculty_id' => null,
                    'registrar_id' => null,
                    'applicant_id' => $genericApplicantId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
