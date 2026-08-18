<?php

namespace App\Support;

use App\Student;
use App\StudentGradeRecord;
use Illuminate\Support\Facades\Schema;

/**
 * Generic token resolver + default starter layout for document types that
 * use the shared DocumentTemplateController editor (as opposed to COR/TOR/
 * Honorable Dismissal, which each keep their own bespoke resolver).
 *
 * To wire up a new document type: add a case in resolve() and defaultLayout(),
 * then whitelist the slug in DocumentTemplateController::$allowedSlugs.
 */
class DocumentTemplateTokens
{
    public static function resolve(string $slug, ?Student $student): array
    {
        $common = self::commonTokens($student);

        switch ($slug) {
            case 'certificate-gwa':
                return array_merge($common, self::gwaTokens($student));
            case 'f137a':
            default:
                return $common;
        }
    }

    public static function defaultLayout(string $slug): array
    {
        return [
            'page' => ['width_mm' => 215.9, 'height_mm' => 279.4, 'orientation' => 'portrait', 'background' => '#ffffff'],
            'elements' => self::defaultElements($slug),
        ];
    }

    private static function commonTokens(?Student $student): array
    {
        if (!$student) {
            return [
                '{{student_no}}' => '-',
                '{{student_name}}' => '-',
                '{{course}}' => '-',
                '{{year_level}}' => '-',
                '{{school_year}}' => '-',
                '{{date}}' => now()->format('F j, Y'),
            ];
        }

        $student->loadMissing(['canonicalCourse', 'academicTerm']);

        $programText = trim((string) $student->program);
        if ($programText === '' && $student->relationLoaded('canonicalCourse')) {
            $programText = trim((string) (optional($student->canonicalCourse)->name ?: optional($student->canonicalCourse)->code));
        }

        $schoolYear = trim((string) $student->school_year) ?: trim((string) optional($student->academicTerm)->school_year);

        return [
            '{{student_no}}' => (string) ($student->student_no ?: '-'),
            '{{student_name}}' => strtoupper(trim((string) $student->name)) ?: '-',
            '{{course}}' => $programText !== '' ? $programText : '-',
            '{{year_level}}' => (string) ($student->year_level ?: '-'),
            '{{school_year}}' => $schoolYear !== '' ? $schoolYear : '-',
            '{{date}}' => now()->format('F j, Y'),
        ];
    }

    private static function gwaTokens(?Student $student): array
    {
        if (!$student || !Schema::hasTable('student_grade_records')) {
            return ['{{gwa}}' => '-'];
        }

        $gradeRecords = StudentGradeRecord::where(function ($q) use ($student) {
            $q->where('student_id', $student->id)
                ->orWhere('student_no', $student->student_no);
        })->get();

        $gradedRecords = $gradeRecords->filter(function ($record) {
            return !$record->inc
                && is_numeric($record->final_grade)
                && (float) $record->final_grade > 0
                && (float) $record->units > 0;
        });

        if ($gradedRecords->isEmpty()) {
            return ['{{gwa}}' => '-'];
        }

        $weightedSum = $gradedRecords->sum(function ($record) {
            return (float) $record->final_grade * (float) $record->units;
        });
        $unitsSum = $gradedRecords->sum(function ($record) {
            return (float) $record->units;
        });

        $gwa = $unitsSum > 0 ? $weightedSum / $unitsSum : null;

        return ['{{gwa}}' => $gwa !== null ? number_format($gwa, 2) : '-'];
    }

    private static function defaultElements(string $slug): array
    {
        if ($slug === 'certificate-gwa') {
            return [
                [
                    'id' => 'gwa_title', 'type' => 'text',
                    'text' => "Certificate of\nGeneral Weighted Average",
                    'top' => 8, 'left' => 10, 'width' => 80,
                    'font_family' => 'Arial', 'font_size' => 20, 'font_weight' => 'bold', 'font_style' => 'normal',
                    'text_decoration' => 'none', 'text_align' => 'center', 'line_height' => 1.3,
                ],
                [
                    'id' => 'gwa_body', 'type' => 'text',
                    'text' => "This certifies that {{student_name}} who has completed all the academic requirements of the {{course}} Program of the Pamantasan ng Lungsod ng Pasig has a General Weighted Average (GWA) of {{gwa}}.\n\nThis certification is being issued upon the request of {{student_name}} for whatever legal purposes it may serve.",
                    'top' => 24, 'left' => 10, 'width' => 80,
                    'font_family' => 'Arial', 'font_size' => 12, 'font_weight' => 'normal', 'font_style' => 'normal',
                    'text_decoration' => 'none', 'text_align' => 'left', 'line_height' => 1.7,
                ],
                [
                    'id' => 'gwa_signature', 'type' => 'text',
                    'text' => "FEDERICO G. NUEVA, MT\nUniversity Registrar",
                    'top' => 62, 'left' => 55, 'width' => 35,
                    'font_family' => 'Arial', 'font_size' => 11, 'font_weight' => 'bold', 'font_style' => 'normal',
                    'text_decoration' => 'none', 'text_align' => 'center', 'line_height' => 1.4,
                ],
            ];
        }

        // f137a and any other not-yet-customized slug get a simple starter block.
        return [
            [
                'id' => 'main_block', 'type' => 'text',
                'text' => "Student No: {{student_no}}\nStudent Name: {{student_name}}\nCourse: {{course}}\nYear Level: {{year_level}}\nSchool Year: {{school_year}}\nDate: {{date}}",
                'top' => 10, 'left' => 8, 'width' => 84,
                'font_family' => 'Arial', 'font_size' => 11, 'font_weight' => 'normal', 'font_style' => 'normal',
                'text_decoration' => 'none', 'text_align' => 'left', 'line_height' => 1.5,
            ],
        ];
    }
}
