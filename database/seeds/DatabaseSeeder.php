<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Registrar;
use App\Faculty;
use App\Department;

require_once __DIR__ . '/SampleUniversalTransmutationSeeder.php';

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
            PlpMasterlistSeeder::class,
            CurriculumAbPsych20252026Seeder::class,
            StudentMasterlistImportSeeder::class,
            ApplicantSeeder::class,
            ApplicantBulkSeeder::class,
            RegistrarAuthSeeder::class,
            FacultyAuthSeeder::class,
            UserAccount3nfSeeder::class,
            ParentAuthSeeder::class,
            AcademicCalendarEventSeeder::class,
            GradeRuleSeeder::class,
            TransmutationRuleSeeder::class,
            SampleUniversalTransmutationSeeder::class,
            GradingPeriodSeeder::class,
            GradingComponentSeeder::class,
            SubjectSeeder::class,
            SystemConfigurationSeeder::class,
            CompactFacultyLoadSeeder::class,
        ]);

        if ((bool) env('SEED_HIGH_VOLUME_TRASH', false)) {
            $this->call([
                HighVolumeSubjectFileSeeder::class,
            ]);
        }

        if ((bool) env('SEED_HIGH_VOLUME_AVAILABLE_SUBJECTS', false)) {
            $this->call([
                HighVolumeAvailableSubjectSeeder::class,
            ]);
        }

        if ((bool) env('SEED_HIGH_VOLUME_STUDENTS', false)) {
            $this->call([
                HighVolumeStudentSeeder::class,
            ]);
        }

        if ((bool) env('SEED_HIGH_VOLUME_STUDENT_DISCIPLINE', false)) {
            $this->call([
                HighVolumeStudentDisciplineSeeder::class,
            ]);
        }

        if ((bool) env('SEED_HIGH_VOLUME_STUDENT_DEFICIENCIES', false)) {
            $this->call([
                HighVolumeStudentDeficiencySeeder::class,
            ]);
        }

        if ((bool) env('SEED_CLASS_LIST_SCHOOL_YEAR_RANGE', false)) {
            $this->call([
                ClassListSchoolYearRangeSeeder::class,
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

        if ((bool) env('SEED_HIGH_VOLUME_TRANSMUTATION', false)) {
            $this->call([
                HighVolumeTransmutationRuleSeeder::class,
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

        $this->call([
            ClassListDataBackfillSeeder::class,
            ClassListStudentEnrollmentBackfillSeeder::class,
            StrayLegacyCourseCleanupSeeder::class,
        ]);

        $this->command->info('Database seeded successfully. Login samples: admin/password, registrar/registrar, faculty/faculty, applicant 2526B0177/PLP-2526B0177, applicant/applicant');
        $this->command->info('Parent sample login: parent/parent (linked to PARENT-0001).');
        $this->command->info('Real students: username = student number, password = PLP-{student_no}, force password reset on first login.');
    }
}
