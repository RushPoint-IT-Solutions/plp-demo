<?php

use App\AcademicTerm;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SystemConfigurationSeeder extends Seeder
{
    public function run()
    {
        $termFirst = $this->resolveAcademicTerm('2025-2026', 'First');
        $termSecond = $this->resolveAcademicTerm('2025-2026', 'Second');

        $this->seedSchoolSemesters([$termFirst, $termSecond]);
        $this->seedGradePostings($termFirst, $termSecond);
        $this->seedNameSignatures();
        $this->seedCutoffEntries($termFirst);
        $this->seedCurriculumDisplay($termFirst);
        $this->seedReportDetails();
        $this->seedEmailSender();

        $this->command->info('SystemConfigurationSeeder complete.');
    }

    private function seedSchoolSemesters(array $terms)
    {
        $table = 'system_school_semesters';
        if (!Schema::hasTable($table)) {
            return;
        }

        foreach ($terms as $term) {
            if (!$term) {
                continue;
            }

            $criteria = $this->academicTermCriteria($table, $term);
            $payload = $this->academicTermPayload($table, $term);

            $this->upsertByCriteria($table, $criteria, $payload);
        }
    }

    private function seedGradePostings(AcademicTerm $termFirst, AcademicTerm $termSecond)
    {
        $table = 'system_grade_postings';
        if (!Schema::hasTable($table)) {
            return;
        }

        $rows = [
            ['term' => $termFirst, 'period' => 'Midterm', 'date_from' => '2025-09-20'],
            ['term' => $termSecond, 'period' => 'Final', 'date_from' => '2026-02-10'],
        ];

        foreach ($rows as $row) {
            $term = $row['term'];
            if (!$term) {
                continue;
            }

            $criteria = $this->academicTermCriteria($table, $term);
            if ($this->hasColumn($table, 'period')) {
                $criteria['period'] = $row['period'];
            }

            $payload = $this->academicTermPayload($table, $term);
            if ($this->hasColumn($table, 'period')) {
                $payload['period'] = $row['period'];
            }
            if ($this->hasColumn($table, 'date_from')) {
                $payload['date_from'] = $row['date_from'];
            }

            $this->upsertByCriteria($table, $criteria, $payload);
        }
    }

    private function seedNameSignatures()
    {
        $designationTable = 'system_config_signature_designations';
        $signatureTable = 'system_config_name_signatures';

        if (!Schema::hasTable($designationTable) || !Schema::hasTable($signatureTable)) {
            return;
        }

        $designationMap = DB::table($designationTable)->pluck('id', 'code')->all();
        $rows = [
            ['code' => 'REGISTRAR', 'name' => 'System Registrar'],
            ['code' => 'ACCOUNTING_HEAD', 'name' => 'Accounting Head'],
            ['code' => 'ASSISTANT_REGISTRAR', 'name' => 'Assistant Registrar'],
        ];

        foreach ($rows as $row) {
            if (!isset($designationMap[$row['code']])) {
                continue;
            }

            $criteria = ['designation_id' => (int) $designationMap[$row['code']]];
            $payload = [];

            if ($this->hasColumn($signatureTable, 'signer_name')) {
                $payload['signer_name'] = $row['name'];
            }

            if ($this->hasColumn($signatureTable, 'signature_path')) {
                $payload['signature_path'] = null;
            }

            if ($this->hasColumn($signatureTable, 'is_active')) {
                $payload['is_active'] = true;
            }

            $this->upsertByCriteria($signatureTable, $criteria, $payload);
        }
    }

    private function seedCutoffEntries(AcademicTerm $term)
    {
        $typeTable = 'system_cutoff_types';
        $entryTable = 'system_cutoff_entries';

        if (!Schema::hasTable($typeTable) || !Schema::hasTable($entryTable)) {
            return;
        }

        $typeMap = DB::table($typeTable)->pluck('id', 'code')->all();

        $rows = [
            ['type' => 'ENROLLMENT', 'cutoff_date' => '2025-08-30', 'event_date' => null, 'student_no' => null, 'notes' => 'Enrollment cut-off.'],
            ['type' => 'FACULTY_LOADING', 'cutoff_date' => '2025-08-28', 'event_date' => null, 'student_no' => null, 'notes' => 'Faculty loading cut-off.'],
            ['type' => 'SECTION_OFFERING', 'cutoff_date' => '2025-08-25', 'event_date' => null, 'student_no' => null, 'notes' => 'Section offering cut-off.'],
            ['type' => 'CHANGING_DELETING_ADDING', 'cutoff_date' => '2025-09-05', 'event_date' => null, 'student_no' => null, 'notes' => 'Changing/deleting/adding cut-off.'],
            ['type' => 'CUT_OFF_REGISTRATION', 'cutoff_date' => '2025-08-26', 'event_date' => '2025-08-20', 'student_no' => '2026A00001', 'notes' => 'Per-student registration cut-off.'],
        ];

        foreach ($rows as $row) {
            if (!isset($typeMap[$row['type']])) {
                continue;
            }

            $criteria = [];
            if ($this->hasColumn($entryTable, 'cutoff_type_id')) {
                $criteria['cutoff_type_id'] = (int) $typeMap[$row['type']];
            }
            if ($this->hasColumn($entryTable, 'cutoff_date')) {
                $criteria['cutoff_date'] = $row['cutoff_date'];
            }
            if ($this->hasColumn($entryTable, 'academic_term_id')) {
                $criteria['academic_term_id'] = (int) $term->id;
            }
            if ($this->hasColumn($entryTable, 'student_no') && !empty($row['student_no'])) {
                $criteria['student_no'] = $row['student_no'];
            }

            $payload = $this->academicTermPayload($entryTable, $term);
            if ($this->hasColumn($entryTable, 'cutoff_type_id')) {
                $payload['cutoff_type_id'] = (int) $typeMap[$row['type']];
            }
            if ($this->hasColumn($entryTable, 'event_date')) {
                $payload['event_date'] = $row['event_date'];
            }
            if ($this->hasColumn($entryTable, 'cutoff_date')) {
                $payload['cutoff_date'] = $row['cutoff_date'];
            }
            if ($this->hasColumn($entryTable, 'student_no')) {
                $payload['student_no'] = $row['student_no'];
            }
            if ($this->hasColumn($entryTable, 'notes')) {
                $payload['notes'] = $row['notes'];
            }

            $this->upsertByCriteria($entryTable, $criteria, $payload);
        }
    }

    private function seedCurriculumDisplay(AcademicTerm $term)
    {
        $table = 'system_curriculum_display_settings';
        if (!Schema::hasTable($table)) {
            return;
        }

        $criteria = $this->academicTermCriteria($table, $term);
        $payload = $this->academicTermPayload($table, $term);

        if ($this->hasColumn($table, 'display_status')) {
            $payload['display_status'] = 'Display';
        }

        $this->upsertByCriteria($table, $criteria, $payload);
    }

    private function seedReportDetails()
    {
        $table = 'system_report_detail_settings';
        if (!Schema::hasTable($table)) {
            return;
        }

        $criteria = [];
        if ($this->hasColumn($table, 'school_id')) {
            $criteria['school_id'] = '000000';
        }

        $payload = [];
        if ($this->hasColumn($table, 'region')) {
            $payload['region'] = 'NCR';
        }
        if ($this->hasColumn($table, 'division')) {
            $payload['division'] = 'Pasig City';
        }
        if ($this->hasColumn($table, 'school_id')) {
            $payload['school_id'] = '000000';
        }
        if ($this->hasColumn($table, 'school_name')) {
            $payload['school_name'] = 'Pamantasan ng Lungsod ng Pasig';
        }
        if ($this->hasColumn($table, 'contact_details')) {
            $payload['contact_details'] = 'registrar@plpasig.edu.ph';
        }
        if ($this->hasColumn($table, 'is_active')) {
            $payload['is_active'] = true;
        }

        $this->upsertByCriteria($table, $criteria, $payload);

        if ($this->hasColumn($table, 'is_active') && $this->hasColumn($table, 'school_id')) {
            DB::table($table)
                ->where('school_id', '!=', '000000')
                ->update(['is_active' => false, 'updated_at' => now()]);
        }
    }

    private function seedEmailSender()
    {
        $table = 'system_email_sender_settings';
        if (!Schema::hasTable($table)) {
            return;
        }

        $criteria = [];
        if ($this->hasColumn($table, 'sender_email')) {
            $criteria['sender_email'] = 'registrar@plpasig.edu.ph';
        }

        $payload = [];
        if ($this->hasColumn($table, 'sender_email')) {
            $payload['sender_email'] = 'registrar@plpasig.edu.ph';
        }
        if ($this->hasColumn($table, 'sender_password_encrypted')) {
            $payload['sender_password_encrypted'] = Crypt::encryptString('ChangeMe123!');
        }
        if ($this->hasColumn($table, 'is_active')) {
            $payload['is_active'] = true;
        }

        $this->upsertByCriteria($table, $criteria, $payload);

        if ($this->hasColumn($table, 'is_active') && $this->hasColumn($table, 'sender_email')) {
            DB::table($table)
                ->where('sender_email', '!=', 'registrar@plpasig.edu.ph')
                ->update(['is_active' => false, 'updated_at' => now()]);
        }
    }

    private function resolveAcademicTerm($schoolYear, $term)
    {
        if (!Schema::hasTable('academic_terms')) {
            return null;
        }

        return AcademicTerm::query()->firstOrCreate(
            ['canonical_key' => strtolower(trim((string) $schoolYear) . '|' . trim((string) $term))],
            ['school_year' => trim((string) $schoolYear), 'term' => trim((string) $term)]
        );
    }

    private function academicTermCriteria($table, AcademicTerm $term)
    {
        $criteria = [];

        if ($this->hasColumn($table, 'academic_term_id')) {
            $criteria['academic_term_id'] = (int) $term->id;
            return $criteria;
        }

        if ($this->hasColumn($table, 'school_year')) {
            $criteria['school_year'] = (string) $term->school_year;
        }

        if ($this->hasColumn($table, 'semester')) {
            $criteria['semester'] = (string) $term->term;
        } elseif ($this->hasColumn($table, 'term')) {
            $criteria['term'] = (string) $term->term;
        }

        return $criteria;
    }

    private function academicTermPayload($table, AcademicTerm $term)
    {
        $payload = [];

        if ($this->hasColumn($table, 'academic_term_id')) {
            $payload['academic_term_id'] = (int) $term->id;
        }

        if ($this->hasColumn($table, 'school_year')) {
            $payload['school_year'] = (string) $term->school_year;
        }

        if ($this->hasColumn($table, 'semester')) {
            $payload['semester'] = (string) $term->term;
        }

        if ($this->hasColumn($table, 'term')) {
            $payload['term'] = (string) $term->term;
        }

        return $payload;
    }

    private function upsertByCriteria($table, array $criteria, array $payload)
    {
        if (!count($criteria)) {
            return;
        }

        $query = DB::table($table);
        foreach ($criteria as $column => $value) {
            $query->where($column, $value);
        }

        $exists = $query->exists();

        if ($exists) {
            if ($this->hasColumn($table, 'updated_at')) {
                $payload['updated_at'] = now();
            }

            $updateQuery = DB::table($table);
            foreach ($criteria as $column => $value) {
                $updateQuery->where($column, $value);
            }
            $updateQuery->update($payload);

            return;
        }

        if ($this->hasColumn($table, 'created_at')) {
            $payload['created_at'] = now();
        }

        if ($this->hasColumn($table, 'updated_at')) {
            $payload['updated_at'] = now();
        }

        DB::table($table)->insert(array_merge($criteria, $payload));
    }

    private function hasColumn($table, $column)
    {
        return Schema::hasTable($table) && Schema::hasColumn($table, $column);
    }
}
