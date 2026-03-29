<?php

use Illuminate\Database\Seeder;
use App\Applicant;
use App\Faculty;
use App\MasterFacultyFile;
use App\MasterStudentProfileFile;
use App\MasterStudentGradeFile;
use App\Student;

class C3TrashDataSeeder extends Seeder
{
    public function run()
    {
        $this->seedFacultyEntities(30);
        $this->seedApplicantEntities(30);
        $this->seedStudentEntities(30);

        $this->seedFacultyRows(30);
        $this->seedStudentProfileRows(30);
        $this->seedStudentGradeRows(30);

        $this->command->info('C3TrashDataSeeder: ensured 30 records each for faculty, applicant, student, and C3 master-file tables.');
    }

    private function seedFacultyEntities(int $target): void
    {
        for ($i = 1; $i <= $target; $i++) {
            Faculty::updateOrCreate([
                'code' => 'TRFAC-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
            ], [
                'name' => 'Trash Faculty Entity ' . $i,
            ]);
        }
    }

    private function seedApplicantEntities(int $target): void
    {
        for ($i = 1; $i <= $target; $i++) {
            Applicant::updateOrCreate([
                'applicant_id' => 'TRAPP26' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
            ], [
                'first_name' => 'TrashApplicant' . $i,
                'last_name' => 'Seed',
                'middle_name' => null,
                'gender' => $i % 2 === 0 ? 'Male' : 'Female',
                'nationality' => 'Filipino',
                'religion' => 'Roman Catholic',
                'civil_status' => 'Single',
                'mobile_number' => '0917' . str_pad((string) $i, 7, '0', STR_PAD_LEFT),
                'email_address' => 'trash.applicant' . $i . '@example.test',
                'exam_result_status' => 'Pending',
            ]);
        }
    }

    private function seedStudentEntities(int $target): void
    {
        $programs = [
            'Bachelor of Science in Computer Science',
            'Bachelor of Science in Information Technology',
            'Bachelor of Science in Information Systems',
            'Bachelor of Science in Computer Engineering',
        ];
        $years = ['First', 'Second', 'Third', 'Fourth'];

        for ($i = 1; $i <= $target; $i++) {
            Student::updateOrCreate([
                'student_no' => 'TRSTD26' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
            ], [
                'name' => 'Trash Student Entity ' . $i,
                'sex' => $i % 2 === 0 ? 'Male' : 'Female',
                'age' => 18 + ($i % 6),
                'college' => 'College of Computer Studies',
                'program' => $programs[$i % count($programs)],
                'curriculum' => '2025',
                'year_level' => $years[$i % count($years)],
                'scholarship' => null,
                'registration_no' => 'TRREG' . str_pad((string) $i, 5, '0', STR_PAD_LEFT),
                'school_year' => '2025-2026',
                'semester' => $i % 2 === 0 ? 'First' : 'Second',
            ]);
        }
    }

    private function seedFacultyRows(int $target): void
    {
        $current = MasterFacultyFile::query()->count();
        if ($current >= $target) {
            return;
        }

        $departments = ['Computer Studies', 'Engineering', 'Business Administration', 'Education'];

        for ($i = $current + 1; $i <= $target; $i++) {
            MasterFacultyFile::create([
                'code' => 'TRF-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'name' => 'Trash Faculty ' . $i,
                'department' => $departments[$i % count($departments)],
                'status' => $i % 5 === 0 ? 'Inactive' : 'Active',
                'config_payload' => null,
            ]);
        }
    }

    private function seedStudentProfileRows(int $target): void
    {
        $current = MasterStudentProfileFile::query()->count();
        if ($current >= $target) {
            return;
        }

        $courses = [
            'Bachelor of Science in Computer Science',
            'Bachelor of Science in Information Technology',
            'Bachelor of Science in Information Systems',
            'Bachelor of Science in Computer Engineering',
        ];
        $years = ['First', 'Second', 'Third', 'Fourth'];

        for ($i = $current + 1; $i <= $target; $i++) {
            MasterStudentProfileFile::create([
                'student_no' => 'TRP26' . str_pad((string) $i, 5, '0', STR_PAD_LEFT),
                'student_name' => 'Trash Profile Student ' . $i,
                'course' => $courses[$i % count($courses)],
                'year_level' => $years[$i % count($years)],
            ]);
        }
    }

    private function seedStudentGradeRows(int $target): void
    {
        $current = MasterStudentGradeFile::query()->count();
        if ($current >= $target) {
            return;
        }

        $courses = [
            'Bachelor of Science in Computer Science',
            'Bachelor of Science in Information Technology',
            'Bachelor of Science in Information Systems',
            'Bachelor of Science in Computer Engineering',
        ];
        $years = ['First', 'Second', 'Third', 'Fourth'];

        for ($i = $current + 1; $i <= $target; $i++) {
            MasterStudentGradeFile::create([
                'student_no' => 'TRG26' . str_pad((string) $i, 5, '0', STR_PAD_LEFT),
                'student_name' => 'Trash Grade Student ' . $i,
                'course' => $courses[$i % count($courses)],
                'year_level' => $years[$i % count($years)],
            ]);
        }
    }
}
