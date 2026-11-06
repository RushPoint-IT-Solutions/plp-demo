<?php

declare(strict_types=1);

namespace App\Services;

use App\Student;
use App\StudentGradeRecord;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class HistoricalStudentGrades
{
    /** Current posted grades take precedence over historical rows for the same subject/term. */
    public function merge(Student $student, Collection $current): Collection
    {
        if (!Schema::hasTable('student_grade_records')) { return $current; }
        $key = function ($row) {
            $subject = $row->subject;
            $term = strtolower(trim((string) optional($subject)->semester));
            $term = ['1st semester' => 'first', '2nd semester' => 'second', 'first semester' => 'first', 'second semester' => 'second'][$term] ?? $term;
            return mb_strtolower(trim((string) optional($subject)->school_year) . '|' . $term . '|' . trim((string) optional($subject)->code), 'UTF-8');
        };
        $seen = [];
        foreach ($current as $row) { $seen[$key($row)] = true; }
        $result = collect($current->all());
        $query = StudentGradeRecord::where(function ($query) use ($student) {
            $query->where('student_id', $student->id)
                ->orWhere(function ($unlinked) use ($student) {
                    $unlinked->whereNull('student_id')->where('student_no', $student->student_no);
                });
        });
        foreach ($query->orderByDesc('school_year')->orderBy('subject_code')->get() as $record) {
            $subject = (object) ['code' => $record->subject_code, 'name' => $record->description, 'units' => $record->units,
                'school_year' => $record->school_year, 'semester' => $record->term, 'section' => $record->section_code,
                'faculty_name' => $record->professor];
            $row = (object) ['id' => $record->id, 'subject' => $subject, 'midterm' => null, 'final' => null,
                'final_average' => $record->final_grade, 'remarks' => $record->remarks ?: $record->grade_status ?: $record->status,
                'midterm_posted_at' => null, 'final_posted_at' => $record->final_grade !== null ? $record->updated_at : null,
                'historical_record' => true, 'historical_grade_status' => $record->grade_status];
            if (!isset($seen[$key($row)])) { $result->push($row); $seen[$key($row)] = true; }
        }
        return $result;
    }
}
