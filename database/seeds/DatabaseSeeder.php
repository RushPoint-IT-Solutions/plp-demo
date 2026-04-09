<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Registrar;
use App\Faculty;
use App\Department;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 1. Core records required by dependent seeders
        Department::updateOrCreate(['code' => 'CS'], [
            'code' => 'CS',
            'description' => 'College of Computer Studies',
        ]);

        $faculty = Faculty::updateOrCreate(['code' => 'FAC-001'], [
            'code' => 'FAC-001',
            'name' => 'Abejo, M.',
        ]);

        $registrar = Registrar::updateOrCreate(['code' => 'REG-001'], [
            'code' => 'REG-001',
            'name' => 'System Registrar',
            'email' => 'registrar@plp.edu.ph',
        ]);

        // 2. Root admin account (not forced to reset) for immediate bootstrap access.
        User::updateOrCreate(['username' => 'admin'], [
            'name' => 'System Registrar',
            'username' => 'admin',
            'email' => 'admin@plp.edu.ph',
            'password' => Hash::make('password'),
            'module' => 'registrar',
            'force_password_reset' => false,
            'student_id' => null,
            'faculty_id' => null,
            'registrar_id' => $registrar->id,
            'applicant_id' => null,
        ]);

        // 3. Ensure fresh installs are fully testable with one command.
        $this->call([
            SemesterSeeder::class,
            YearBlockSeeder::class,
            CourseCatalogSeeder::class,
            CourseCurriculumYearSeeder::class,
            ApplicantSeeder::class,
            ApplicantBulkSeeder::class,
            RegistrarAuthSeeder::class,
            FacultyAuthSeeder::class,
            StudentSeeder::class,
            StudentFirstLoginDemoSeeder::class,
            AcademicCalendarEventSeeder::class,
            GradeRuleSeeder::class,
            TransmutationRuleSeeder::class,
            GradingPeriodSeeder::class,
            GradingComponentSeeder::class,
            StudentDeficiencySeeder::class,
            SubjectSeeder::class,
            CourseCurriculumSubjectMatrixSeeder::class,
            FacultySeeder::class,
            StudentDemoDataSeeder::class,
            C3TrashDataSeeder::class,
            RegistrarRequirement3nfSeeder::class,
        ]);

        if ((bool) env('SEED_HIGH_VOLUME_TRASH', false)) {
            $this->call([
                HighVolumeSubjectFileSeeder::class,
            ]);
        }

        if ((bool) env('SEED_HIGH_VOLUME_PROGRAMS', false)) {
            $this->call([
                HighVolumeProgramFileSeeder::class,
            ]);
        }

        if ((bool) env('SEED_HIGH_VOLUME_ROOMS', false)) {
            $this->call([
                HighVolumeRoomFileSeeder::class,
            ]);
        }

        if ((bool) env('SEED_HIGH_VOLUME_SLOTS', false)) {
            $this->call([
                HighVolumeSlotMonitoringSeeder::class,
            ]);
        }

        if ((bool) env('SEED_HIGH_VOLUME_SECTION_MERGING', false)) {
            $this->call([
                HighVolumeSectionMergingSeeder::class,
            ]);
        }
        
        $this->command->info('Database seeded successfully. Login samples: admin/password, registrar/registrar, faculty/faculty, student/student, first-reset student 2026A00001/PLP-2026A00001, applicant 2526B0177/PLP-2526B0177, applicant/applicant');
    }
}
