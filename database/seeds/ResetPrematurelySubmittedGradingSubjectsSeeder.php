<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Repairs subjects caught by a prior bug in FacultyController::submitGrades()
 * where posting Midterm alone (not just Final) marked the whole subject as
 * SUBMITTED/DEAN_APPROVED/REGISTRAR_FINALIZED, locking the Final column
 * before any final grades were ever posted.
 *
 * Targets only subjects where at least one student has midterm_posted_at
 * set but zero students have final_posted_at set, and the subject's status
 * is past "Open For Encoding" — resets them back to Open For Encoding so
 * Final grades can be entered.
 */
class ResetPrematurelySubmittedGradingSubjectsSeeder extends Seeder
{
    public function run()
    {
        $openStatusId = DB::table('subject_grading_statuses')
            ->whereRaw('UPPER(code) = ?', ['OPEN FOR ENCODING'])
            ->value('id');

        if (!$openStatusId) {
            $this->command->error('Could not find "Open For Encoding" grading status. Aborting.');
            return;
        }

        $affectedSubjectIds = DB::table('subjects as s')
            ->join('subject_grading_statuses as gs', 'gs.id', '=', 's.grading_status_id')
            ->whereIn('gs.code', ['SUBMITTED', 'DEAN_APPROVED', 'REGISTRAR_FINALIZED'])
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('student_subject_grades as ssg')
                    ->whereColumn('ssg.subject_id', 's.id')
                    ->whereNotNull('ssg.midterm_posted_at');
            })
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('student_subject_grades as ssg')
                    ->whereColumn('ssg.subject_id', 's.id')
                    ->whereNotNull('ssg.final_posted_at');
            })
            ->pluck('s.id');

        if ($affectedSubjectIds->isEmpty()) {
            $this->command->info('No prematurely submitted subjects found.');
            return;
        }

        DB::table('subjects')
            ->whereIn('id', $affectedSubjectIds)
            ->update([
                'grading_status_id' => $openStatusId,
                'submitted_at' => null,
                'dean_approved_by' => null,
                'dean_approved_at' => null,
                'dean_approved_by_name' => null,
                'registrar_finalized_by' => null,
                'registrar_finalized_at' => null,
                'registrar_finalized_by_name' => null,
                'grading_returned_by' => null,
                'grading_returned_at' => null,
                'grading_return_reason' => null,
                'updated_at' => now(),
            ]);

        $this->command->info('Reset ' . $affectedSubjectIds->count() . ' subject(s): ' . $affectedSubjectIds->implode(', '));
    }
}
