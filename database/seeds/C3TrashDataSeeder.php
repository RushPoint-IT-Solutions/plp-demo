<?php

use Illuminate\Database\Seeder;
use App\MasterFacultyFile;
use App\MasterStudentProfileFile;
use App\MasterStudentGradeFile;

class C3TrashDataSeeder extends Seeder
{
    public function run()
    {
        $this->seedFacultyRows(30);
        $this->seedStudentProfileRows(30);
        $this->seedStudentGradeRows(30);

        $this->command->info('C3TrashDataSeeder: ensured 30 records each for faculty file, student profile, and student grade file.');
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
