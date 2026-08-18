<?php

namespace App\Support;

use App\DocumentTemplate;
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
            'headerElements' => self::corHeaderElements($student),
        ];
    }

    /**
     * Mirrors RegistrarController::getCorTemplateLayout()/resolveCorTokens() so
     * the batch print sheet reuses the same saved COR header layout/tokens as
     * the single-student print page instead of a hand-built header.
     */
    public static function corHeaderElements(Student $student): array
    {
        if (!Schema::hasTable('document_templates')) {
            return [];
        }

        $template = DocumentTemplate::firstOrCreate(
            ['slug' => 'cor'],
            ['name' => 'Certificate of Registration (header)', 'content_json' => self::defaultCorTemplateLayout()]
        );

        $layout = $template->content_json ?: self::defaultCorTemplateLayout();

        $prof = $student->profile;

        $formattedSchoolYear = trim((string) $student->school_year);
        if ($formattedSchoolYear === '' && $student->relationLoaded('academicTerm')) {
            $formattedSchoolYear = trim((string) optional($student->academicTerm)->school_year);
        }
        $formattedSemester = strtoupper(trim((string) $student->semester));
        if ($formattedSemester === '' && $student->relationLoaded('academicTerm')) {
            $formattedSemester = strtoupper(trim((string) optional($student->academicTerm)->term));
        }
        $semesterLabel = $formattedSemester !== ''
            ? (strpos($formattedSemester, 'SEMESTER') !== false ? $formattedSemester : $formattedSemester . ' SEMESTER')
            : '-';
        $academicYearLabel = $formattedSchoolYear !== '' ? $formattedSchoolYear : '-';
        $schoolYearLabel = $academicYearLabel . ($semesterLabel !== '-' ? ' / ' . $semesterLabel : '');

        $programText = trim((string) $student->program);
        if ($programText === '' && $student->relationLoaded('canonicalCourse')) {
            $programText = trim((string) (optional($student->canonicalCourse)->name ?: optional($student->canonicalCourse)->code));
        }
        $programText = $programText !== '' ? $programText : '-';

        $addressParts = array_filter([
            trim((string) optional($prof)->present_street),
            trim((string) optional($prof)->present_barangay),
            trim((string) optional($prof)->present_municipality),
            trim((string) optional($prof)->present_province),
        ]);
        $addressText = count($addressParts) ? implode(', ', $addressParts) : trim((string) $student->address);
        $addressText = $addressText !== '' ? $addressText : '-';

        $tokens = [
            '{{enrollment_no}}' => (string) ($student->registration_no ?: $student->id ?: '-'),
            '{{student_no}}' => (string) ($student->student_no ?: '-'),
            '{{student_name}}' => strtoupper(trim((string) $student->name)) ?: '-',
            '{{address}}' => $addressText,
            '{{course}}' => $programText,
            '{{department}}' => (string) ($student->college ?: $student->department ?: '-'),
            '{{enrollment_date}}' => optional($student->created_at)->format('m/d/Y') ?: '-',
            '{{curriculum}}' => (string) ($student->curriculum ?: '-'),
            '{{school_year}}' => $schoolYearLabel,
            '{{year_level}}' => (string) ($student->year_level ?: '-'),
            '{{student_type}}' => (string) ($student->student_type ?: 'Old Student'),
            '{{adjustment_no}}' => (string) ($student->adjustment_no ?: $student->registration_no ?: '-'),
            '{{scholarship}}' => 'UNIFIED FINANCIAL ASSISTANCE FOR TERTIARY EDUCATION',
        ];

        $elements = $layout['elements'] ?? [];
        foreach ($elements as $index => $element) {
            $text = (string) ($element['text'] ?? '');
            $elements[$index]['resolved_text'] = strtr($text, $tokens);
        }

        return $elements;
    }

    private static function defaultCorTemplateLayout(): array
    {
        return [
            'page' => ['width_mm' => 215.9, 'height_mm' => 355.6, 'orientation' => 'portrait', 'background' => '#ffffff'],
            'elements' => [
                ['id' => 'meta_col1', 'type' => 'text', 'text' => "Enrollment No: {{enrollment_no}}\nStudent No: {{student_no}}\nStudent Name: {{student_name}}\nAddress: {{address}}\nCourse: {{course}}\nDepartment: {{department}}", 'top' => 2, 'left' => 4, 'width' => 60, 'font_family' => 'Arial', 'font_size' => 8, 'font_weight' => 'normal', 'font_style' => 'normal', 'text_decoration' => 'none', 'text_align' => 'left', 'line_height' => 1.5],
                ['id' => 'meta_col2', 'type' => 'text', 'text' => "Enrollment Date: {{enrollment_date}}\nCurriculum: {{curriculum}}\nSchool Year: {{school_year}}", 'top' => 2, 'left' => 66, 'width' => 34, 'font_family' => 'Arial', 'font_size' => 8, 'font_weight' => 'normal', 'font_style' => 'normal', 'text_decoration' => 'none', 'text_align' => 'left', 'line_height' => 1.5],
                ['id' => 'meta_col3', 'type' => 'text', 'text' => "Year Level: {{year_level}}\nStudent Type: {{student_type}}\nAdjustment No: {{adjustment_no}}", 'top' => 46, 'left' => 66, 'width' => 34, 'font_family' => 'Arial', 'font_size' => 8, 'font_weight' => 'normal', 'font_style' => 'normal', 'text_decoration' => 'none', 'text_align' => 'left', 'line_height' => 1.5],
                ['id' => 'scholarship_line', 'type' => 'text', 'text' => "Scholarship/Grant:  {{scholarship}}", 'top' => 82, 'left' => 4, 'width' => 92, 'font_family' => 'Arial', 'font_size' => 8, 'font_weight' => 'bold', 'font_style' => 'normal', 'text_decoration' => 'none', 'text_align' => 'left', 'line_height' => 1.2],
            ],
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
