<?php

namespace App\Support;

use App\GraduateTagging;
use App\Student;
use App\StudentGradeRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CorTorReportData
{
    public static function corForStudent(Student $student): array
    {
        $student->loadMissing(['subjects', 'profile', 'canonicalCourse', 'yearBlock', 'academicTerm']);

        $subjects = $student->subjects->values();
        $totalUnits = (float) $subjects->sum(function ($subject) {
            return is_numeric($subject->units) ? (float) $subject->units : 0;
        });

        return [
            'student' => $student,
            'subjects' => $subjects,
            'totalUnits' => $totalUnits,
            'assessment' => self::buildCorAssessment($subjects, $totalUnits),
        ];
    }

    public static function torForStudent(Student $student, string $purpose = 'FOR EVALUATION PURPOSES ONLY'): array
    {
        $student->loadMissing(['profile', 'canonicalCourse']);

        $gradeRecords = collect([]);
        if (Schema::hasTable('student_grade_records')) {
            $gradeRecords = StudentGradeRecord::where(function ($q) use ($student) {
                $q->where('student_id', $student->id)
                    ->orWhere('student_no', $student->student_no);
            })->orderBy('school_year')->orderBy('term')->orderBy('subject_code')->get();
        }

        $gradesBySyTerm = $gradeRecords->groupBy(function ($r) {
            return $r->school_year . '|||' . $r->term;
        });

        $gradedRecords = $gradeRecords->filter(function ($r) {
            return !$r->inc && is_numeric($r->final_grade) && (float) $r->final_grade > 0 && (float) $r->units > 0;
        });

        $gwa = null;
        if ($gradedRecords->count() > 0) {
            $tw = $gradedRecords->sum(function ($r) { return (float) $r->final_grade * (float) $r->units; });
            $tu = $gradedRecords->sum(function ($r) { return (float) $r->units; });
            $gwa = $tu > 0 ? round($tw / $tu, 4) : null;
        }

        $totalUnitsEarned = $gradedRecords->filter(function ($r) { return (float) $r->final_grade <= 3.0; })
            ->sum(function ($r) { return (float) $r->units; });

        $signatories = collect([]);
        if (Schema::hasTable('system_config_name_signatures') && Schema::hasTable('system_config_signature_designations')) {
            $signatories = DB::table('system_config_name_signatures as s')
                ->join('system_config_signature_designations as d', 's.designation_id', '=', 'd.id')
                ->where('s.is_active', 1)
                ->orderBy('d.sort_order')
                ->select('s.signer_name', 's.signature_path', 'd.name as designation_name', 'd.code')
                ->get();
        }

        $registrar = $signatories->first(function ($s) {
            return stripos($s->designation_name, 'university registrar') !== false;
        }) ?? $signatories->first(function ($s) {
            return stripos($s->designation_name, 'registrar') !== false;
        }) ?? $signatories->first();

        $assistantRegistrar = $signatories->first(function ($s) {
            return stripos($s->designation_name, 'assistant registrar') !== false;
        });

        $graduateTagging = null;
        if (Schema::hasTable('graduate_taggings')) {
            $graduateTagging = GraduateTagging::where('student_id', $student->id)->first();
        }

        return [
            'student' => $student,
            'gradeRecords' => $gradeRecords,
            'gradesBySyTerm' => $gradesBySyTerm,
            'gwa' => $gwa,
            'totalUnitsEarned' => $totalUnitsEarned,
            'registrar' => $registrar,
            'assistantRegistrar' => $assistantRegistrar,
            'graduateTagging' => $graduateTagging,
            'isGraduated' => $graduateTagging && $graduateTagging->is_graduate,
            'purpose' => $purpose,
        ];
    }

    private static function buildCorAssessment($subjects, float $totalUnits): array
    {
        $nstpUnits = (float) $subjects->sum(function ($subject) {
            $code = strtoupper((string) $subject->code);
            $name = strtoupper((string) $subject->name);

            if (strpos($code, 'NSTP') !== false || strpos($name, 'CWTS') !== false || strpos($name, 'ROTC') !== false) {
                return is_numeric($subject->units) ? (float) $subject->units : 0;
            }

            return 0;
        });

        $tuitionUnits = max($totalUnits - $nstpUnits, 0);
        $perUnitRate = 50.0;
        $miscellaneousFee = 300.0;
        $laboratoryFee = 500.0;

        $tuitionFee = $tuitionUnits * $perUnitRate;
        $cwtsFee = $nstpUnits * $perUnitRate;
        $totalTuitionFee = $tuitionFee + $cwtsFee;
        $currentAccount = $totalTuitionFee + $miscellaneousFee + $laboratoryFee;

        return [
            'tuition_units' => $tuitionUnits,
            'nstp_units' => $nstpUnits,
            'per_unit_rate' => $perUnitRate,
            'tuition_fee' => $tuitionFee,
            'cwts_fee' => $cwtsFee,
            'total_tuition_fee' => $totalTuitionFee,
            'miscellaneous_fee' => $miscellaneousFee,
            'laboratory_fee' => $laboratoryFee,
            'current_account' => $currentAccount,
        ];
    }
}
