<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$applicantCode = isset($argv[1]) ? $argv[1] : '2526B0177';

$applicant = App\Applicant::where('applicant_id', $applicantCode)->first();
if (!$applicant) {
    fwrite(STDERR, "Applicant not found: {$applicantCode}" . PHP_EOL);
    exit(1);
}

Illuminate\Support\Facades\DB::table('applicant_educational_backgrounds')
    ->where('applicant_id', $applicant->id)
    ->delete();

Illuminate\Support\Facades\DB::table('applicant_family_backgrounds')
    ->where('applicant_id', $applicant->id)
    ->delete();

Illuminate\Support\Facades\DB::table('applicant_application_preferences')
    ->where('applicant_id', $applicant->id)
    ->delete();

$applicant->application_status = 'draft';
$applicant->application_draft_step = 1;
$applicant->application_submitted_at = null;
$applicant->application_portal_stage = 0;
$applicant->exam_date = null;
$applicant->exam_room = null;
$applicant->exam_result_status = 'Pending';
$applicant->exam_score = null;
$applicant->save();

echo 'Reset complete for applicant_id=' . $applicant->applicant_id . PHP_EOL;
