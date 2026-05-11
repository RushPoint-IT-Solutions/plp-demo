@extends('layouts.student')

@section('title', 'Application For Change Of Grade - PLP')
@section('page-title', 'FORMS')
@section('body-class', 'page-student-forms')

@section('content')
@php
    $displayName = trim(($profile->first_name ?? '') . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? ''));
    $displayStudentNo = $profile->student_no ?? optional($student)->student_no;
    $selectedSubject = $selectedChangeGrade ? $selectedChangeGrade->subject : null;
    $selectedFaculty = trim((string) (optional(optional($selectedSubject)->facultyModel)->name ?: optional($selectedSubject)->faculty));
    $selectedCourseCode = (string) optional($selectedSubject)->code;
    $selectedCourseName = (string) optional($selectedSubject)->name;
    $selectedProgram = trim((string) (optional($student)->program ?: optional($student)->college));
    $selectedYearSection = trim((string) (optional($selectedSubject)->year_section ?: optional($student)->year_level));
    $selectedProgramYearSection = trim($selectedProgram . ' ' . $selectedYearSection);
    $selectedTerm = trim(preg_replace('/\bsemester\b/i', '', (string) (optional($selectedSubject)->semester ?: optional($student)->semester)));
    $selectedSchoolYear = optional($selectedSubject)->school_year ?: optional($student)->school_year;
    $hasFinalGrade = $selectedChangeGrade && $selectedChangeGrade->final !== null && $selectedChangeGrade->final !== '';
    $hasMidtermGrade = $selectedChangeGrade && $selectedChangeGrade->midterm !== null && $selectedChangeGrade->midterm !== '';
    $selectedGradePeriod = $hasFinalGrade ? 'Final' : ($hasMidtermGrade ? 'Midterm' : '');
    $selectedOldGrade = $selectedChangeGrade && $selectedChangeGrade->final_average !== null
        ? number_format((float) $selectedChangeGrade->final_average, 2)
        : ($hasFinalGrade
            ? number_format((float) $selectedChangeGrade->final, 2)
            : ($hasMidtermGrade ? number_format((float) $selectedChangeGrade->midterm, 2) : ''));
    $selectedMidterm = $hasMidtermGrade ? number_format((float) $selectedChangeGrade->midterm, 2) : '';
    $selectedFinal = $hasFinalGrade ? number_format((float) $selectedChangeGrade->final, 2) : '';
    $selectedSemestral = $selectedChangeGrade && $selectedChangeGrade->final_average !== null ? number_format((float) $selectedChangeGrade->final_average, 2) : '';
@endphp
<div class="cor-scroll-wrapper acd-page">
    <div class="acd-canvas">
        <div class="acd-actions">
            @if(isset($changeGradeRows) && $changeGradeRows->count())
                <form method="GET" action="{{ route('student.forms.show', 'change-grade') }}" class="acd-form-picker">
                    <select name="grade_id" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="Select subject to pre-fill">
                        @foreach($changeGradeRows as $gradeRow)
                            @php($subjectOption = $gradeRow->subject)
                            <option value="{{ $gradeRow->id }}" {{ $selectedChangeGrade && (int) $selectedChangeGrade->id === (int) $gradeRow->id ? 'selected' : '' }}>
                                {{ trim((optional($subjectOption)->code ?: 'Subject') . ' - ' . (optional($subjectOption)->name ?: 'Grade Record')) }}
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif
            <button id="acd-print-btn" type="button" class="btn btn-sm acd-print-btn">Print Form</button>
        </div>

        <div class="cor-container acd-form cog-form">
            <div class="cog-body">
                <p class="cog-form-no-header" style="text-align: left; margin: 0 0 8px;">PLPRO FORM NO. 3F REVISED 2023</p>
                <div class="comp-date-wrap">
                    <p class="cog-date">Date of Application: <input type="text" class="cog-box cog-box--lg acd-inline-input no-print-underline" value="{{ now()->format('F d, Y') }}"></p>
                </div>

                <p class="cog-line-row">
                    <span>This is to certify that the ({{ $selectedGradePeriod === 'Midterm' ? 'X' : ' ' }}) Midterm ({{ $selectedGradePeriod === 'Final' ? 'X' : ' ' }}) Final grade of (student's name)</span>
                    <input type="text" class="cog-box cog-box--xl acd-inline-input" value="{{ $displayName ?: optional($student)->name }}">
                </p>
                <p class="cog-line-row cog-line-row--details">
                    <span>with student number</span>
                    <input type="text" class="cog-box cog-box--sm acd-inline-input" value="{{ $displayStudentNo }}">
                    <span>of (Program, Year &amp; Section)</span>
                    <input type="text" class="cog-box cog-box--prog acd-inline-input" value="{{ $selectedProgramYearSection }}">
                    <span>in (course code &amp; description)</span>
                    <span class="cog-course-combo">
                        <input type="text" class="cog-box cog-box--course-short acd-inline-input" value="{{ $selectedCourseCode }}">
                        <input type="text" class="cog-box cog-box--course-long acd-inline-input" value="{{ $selectedCourseName }}">
                    </span>
                    <span>for the</span>
                    <input type="text" class="cog-box cog-box--sm acd-inline-input" value="{{ $selectedTerm }}">
                    <span>Semester of Academic Year</span>
                    <input type="text" class="cog-box cog-box--sm acd-inline-input" value="{{ $selectedSchoolYear }}">
                </p>
                <p class="cog-line-row">
                    <span>Grade has been changed from</span>
                    <input type="text" class="cog-box cog-box--sm acd-inline-input" value="{{ $selectedOldGrade }}">
                    <span>to</span>
                    <input type="text" class="cog-box cog-box--sm acd-inline-input" value="">
                    <span>due to the following reason(s):</span>
                    <input type="text" class="acd-inline-input cog-line-fill" value="">
                </p>
                <p><input type="text" class="acd-inline-input cog-line-fill cog-line-fill--full" value=""></p>

                <div class="cog-computation">
                    <p class="cog-subtitle">NEW SEMESTRAL GRADE COMPUTATION:</p>
                    <table>
                        <thead>
                            <tr><th></th><th>PERCENTAGE</th><th>GRADE POINT</th></tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>MIDTERM GRADE</td>
                                <td><input type="text" class="acd-cell-input" aria-label="Midterm percentage" value="{{ $selectedMidterm !== '' ? '50%' : '' }}"></td>
                                <td><input type="text" class="acd-cell-input" aria-label="Midterm grade point" value="{{ $selectedMidterm }}"></td>
                            </tr>
                            <tr>
                                <td>FINAL GRADE</td>
                                <td><input type="text" class="acd-cell-input" aria-label="Final percentage" value="{{ $selectedFinal !== '' ? '50%' : '' }}"></td>
                                <td><input type="text" class="acd-cell-input" aria-label="Final grade point" value="{{ $selectedFinal }}"></td>
                            </tr>
                            <tr>
                                <td>SEMESTRAL</td>
                                <td><input type="text" class="acd-cell-input" aria-label="Semestral percentage" value="{{ $selectedSemestral !== '' ? '100%' : '' }}"></td>
                                <td><input type="text" class="acd-cell-input" aria-label="Semestral grade point" value="{{ $selectedSemestral }}"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="cog-signatures">
                    <div><p>Requested by:</p><p class="cog-line"><input type="text" class="acd-inline-input acd-inline-input--lg" value="{{ $selectedFaculty }}"></p><p><strong>Faculty</strong></p></div>
                    <div><p>Conforme:</p><p class="cog-line"><input type="text" class="acd-inline-input acd-inline-input--lg" value="{{ $displayName ?: optional($student)->name }}"></p><p><strong>Student</strong></p></div>
                    <div><p>Approved by:</p><p class="cog-line"><input type="text" class="acd-inline-input acd-inline-input--lg"></p><p><strong>College Dean of Student</strong></p></div>
                    <div><p>Approved by:</p><p class="cog-line"><input type="text" class="acd-inline-input acd-inline-input--lg"></p><p><strong>College Dean of Faculty</strong></p></div>
                    <div><p>Noted by:</p><p class="cog-line"><input type="text" class="acd-inline-input acd-inline-input--lg"></p><p><strong>University Registrar</strong></p></div>
                </div>

                <p class="cog-attachment">Attachment:<br>1. Class Record of the Faculty</p>
                <div class="cog-cut-divider"></div>
            </div>
        </div>
    </div>
</div>
@endsection
