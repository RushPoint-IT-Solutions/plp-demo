@extends('layouts.registrar')

@section('title', 'PLP - Application for Leave of Absence - Enrolled')
@section('page-title', 'APPLICATION FOR LEAVE OF ABSENCE - ENROLLED')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/loa-enrolled.css') }}?v={{ time() }}">
@endpush

@section('content')
@php
    $selectedStudent = $student;
    $studentName = optional($selectedStudent)->name ?: '';
    $studentNo = optional($selectedStudent)->student_no ?: '';
    $program = optional($selectedStudent)->program ?: '';
    $schoolYear = optional($selectedStudent)->school_year ?: '';
    $semester = optional($selectedStudent)->semester ?: '';

    $displayRows = collect($gradeRows ?? [])->take(8)->values()->all();
    $blankRow = [
        'course_code' => '',
        'course_description' => '',
        'section' => '',
        'midterm_grade' => '',
        'final_grade' => '',
        'semestral_grade_remarks' => '',
        'professor_name_signature' => '',
    ];

    while (count($displayRows) < 8) {
        $displayRows[] = $blankRow;
    }
@endphp

<div class="loae-page">
    <div class="loae-toolbar d-print-none">
        <form method="GET" action="{{ route('registrar.registrar-menu.forms.application-leave-of-absence-enrolled') }}" class="loae-toolbar__form">
            <label for="loae-student-id" class="loae-toolbar__label">Student</label>
            <select name="student_id" id="loae-student-id" class="form-select loae-toolbar__select">
                @forelse($students as $optionStudent)
                    <option value="{{ $optionStudent->id }}" {{ (int) $selectedStudentId === (int) $optionStudent->id ? 'selected' : '' }}>
                        {{ $optionStudent->student_no }} - {{ $optionStudent->name }}
                    </option>
                @empty
                    <option value="">No student records found</option>
                @endforelse
            </select>
        </form>

        <div class="loae-toolbar__actions">
            <button type="button" id="loae-print-btn" class="btn btn-outline-secondary loae-toolbar__button">Print</button>
        </div>
    </div>

    <article class="loae-sheet" aria-label="Application for Leave of Absence - Enrolled">
        <p class="loae-form-no">PLPRO FORM NO. IH-2 Revised 2023</p>
        <h2 class="loae-title">APPLICATION FOR LEAVE OF ABSENCE - ENROLLED</h2>

        <div class="loae-date-row">
            <span class="loae-date-label">Date of Application</span>
            <input type="text" class="loae-inline loae-inline--date loae-inline--center" value="{{ $applicationDate }}">
        </div>

        <p class="loae-paragraph">
            I, <input type="text" class="loae-inline loae-inline--name" value="{{ $studentName }}">,
            a student currently enrolled in Pamantasan ng Lungsod ng Pasig with student number
            <input type="text" class="loae-inline loae-inline--student-no" value="{{ $studentNo }}">
            under the BS <input type="text" class="loae-inline loae-inline--program" value="{{ $program }}"> Program,
            hereby request for the withdrawal of my enrolment and application for a leave of absence effective this
            <input type="text" class="loae-inline loae-inline--semester" value="{{ $semester }}"> sem AY
            <input type="text" class="loae-inline loae-inline--sy" value="{{ $schoolYear }}"> due to the following reason(s):
        </p>

        <div class="loae-reasons">
            <label class="loae-reason-item"><input type="checkbox"> medical condition</label>
            <label class="loae-reason-item"><input type="checkbox"> financial constraint</label>
            <label class="loae-reason-item"><input type="checkbox"> unavailability of subject</label>
            <label class="loae-reason-item"><input type="checkbox"> work</label>
            <label class="loae-reason-item"><input type="checkbox"> pregnancy</label>
            <label class="loae-reason-item"><input type="checkbox"> family problem</label>
            <label class="loae-reason-item loae-reason-item--others"><input type="checkbox"> others <input type="text" class="loae-inline loae-inline--others" value=""></label>
        </div>

        <p class="loae-paragraph loae-paragraph--agreement">
            I hereby agree that should I fail to return to the University to resume my studies at the expiration of my leave of absence,
            and without an extension of such leave of absence approved in writing, I shall be dropped from the rolls of the University.
        </p>

        <div class="loae-signature-block">
            <input type="text" class="loae-inline loae-inline--signature loae-inline--center" value="{{ $studentName }}">
            <p class="loae-caption">Student Signature above printed name</p>
            <p class="loae-student-id-line">Student No: <input type="text" class="loae-inline loae-inline--student-no" value="{{ $studentNo }}"></p>
        </div>

        <div class="loae-divider loae-divider--dotted"></div>

        <p class="loae-table-lead">The following grade/remarks will be accorded should the student pursue to file Leave of Absence:</p>

        <table class="loae-grade-table">
            <thead>
                <tr>
                    <th>COURSE CODE</th>
                    <th>COURSE DESCRIPTION</th>
                    <th>SECTION</th>
                    <th>MIDTERM GRADE</th>
                    <th>FINAL GRADE</th>
                    <th>SEMESTRAL GRADE/REMARKS</th>
                    <th>PROFESSOR'S NAME &amp; SIGNATURE</th>
                </tr>
            </thead>
            <tbody>
                @foreach($displayRows as $row)
                    <tr>
                        <td><input type="text" class="loae-cell-input" value="{{ $row['course_code'] }}"></td>
                        <td><input type="text" class="loae-cell-input loae-cell-input--left" value="{{ $row['course_description'] }}"></td>
                        <td><input type="text" class="loae-cell-input" value="{{ $row['section'] }}"></td>
                        <td><input type="text" class="loae-cell-input" value="{{ $row['midterm_grade'] }}"></td>
                        <td><input type="text" class="loae-cell-input" value="{{ $row['final_grade'] }}"></td>
                        <td><input type="text" class="loae-cell-input loae-cell-input--left" value="{{ $row['semestral_grade_remarks'] }}"></td>
                        <td><input type="text" class="loae-cell-input loae-cell-input--left" value="{{ $row['professor_name_signature'] }}"></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="loae-divider loae-divider--dotted"></div>

        <div class="loae-grid loae-grid--two loae-grid--attestation">
            <section class="loae-box loae-box--dsa">
                <p>This is to attest that the student did not commit any offenses stipulated in the student manual nor make any derogatory act contrary to the university's name, reputation and ideals.</p>
                <p class="loae-sign-line"><input type="text" class="loae-inline loae-inline--line" value=""></p>
                <p class="loae-sign-role">DSA Director</p>
            </section>

            <section class="loae-box loae-box--guidance">
                <p class="loae-box-title">FOR FAMILY PROBLEMS/WORK/FINANCIAL CONSTRAINTS REASONS ONLY:</p>
                <p>Counseled:</p>
                <p class="loae-sign-line"><input type="text" class="loae-inline loae-inline--line" value=""></p>
                <p class="loae-sign-role">Guidance Counselor</p>
            </section>
        </div>

        <div class="loae-grid loae-grid--two loae-grid--stacked loae-grid--clearance">
            <section class="loae-box loae-box--medical">
                <p class="loae-box-title">FOR MEDICAL CONDITION/PREGNANCY REASONS ONLY:</p>
                <p>Student's Health Condition: <input type="text" class="loae-inline loae-inline--line" value=""></p>
                <p>REMARKS: <label class="loae-inline-check"><input type="checkbox"> LOA NECESSARY</label> <label class="loae-inline-check"><input type="checkbox"> LOA NOT NECESSARY</label></p>
                <p class="loae-sign-line"><input type="text" class="loae-inline loae-inline--line" value=""></p>
                <p class="loae-sign-role">Medical Officer</p>
            </section>

            <section class="loae-box loae-box--unavailability">
                <p class="loae-box-title">FOR UNAVAILABILITY OF SUBJECT REASON ONLY:</p>
                <p>This is to attest that the student has no subject to enroll this semester.</p>
                <p class="loae-sign-line"><input type="text" class="loae-inline loae-inline--line" value=""></p>
                <p class="loae-sign-role">Registrar's Office College Secretary</p>
            </section>
        </div>

        <div class="loae-grid loae-grid--four loae-grid--staff">
            <section class="loae-box loae-box--dean-metrics">
                <p class="loae-box-title">To be accomplished by the Dean:</p>
                <table class="loae-mini-table">
                    <tbody>
                        <tr><td>No. of Failed and UD Grades</td><td><input type="text" class="loae-mini-input" value=""></td></tr>
                        <tr><td>Max. Residency Yrs</td><td><input type="text" class="loae-mini-input" value=""></td></tr>
                        <tr><td>No. of Yrs Enrolled</td><td><input type="text" class="loae-mini-input" value=""></td></tr>
                        <tr><td>No. of Remaining Yrs</td><td><input type="text" class="loae-mini-input" value=""></td></tr>
                        <tr><td>Will Require Extension of Residency Yrs</td><td><label class="loae-inline-check"><input type="checkbox"> Y</label><label class="loae-inline-check"><input type="checkbox"> N</label></td></tr>
                        <tr><td>All subjects are still offered upon projected return</td><td><label class="loae-inline-check"><input type="checkbox"> Y</label><label class="loae-inline-check"><input type="checkbox"> N</label></td></tr>
                    </tbody>
                </table>
            </section>

            <section class="loae-box loae-box--approval">
                <p><label class="loae-inline-check"><input type="checkbox"> LOA Approved</label></p>
                <p><label class="loae-inline-check"><input type="checkbox"> LOA Disapproved</label></p>
                <p class="loae-sign-line loae-sign-line--spaced"><input type="text" class="loae-inline loae-inline--line" value=""></p>
                <p class="loae-sign-role">College Dean</p>
                <p class="loae-sign-date">Date <input type="text" class="loae-inline loae-inline--date-small" value=""></p>
            </section>

            <section class="loae-box loae-box--noted">
                <p>Noted:</p>
                <p class="loae-sign-line loae-sign-line--spaced"><input type="text" class="loae-inline loae-inline--line" value=""></p>
                <p class="loae-sign-role">University Registrar</p>
                <p class="loae-sign-date">Date <input type="text" class="loae-inline loae-inline--date-small" value=""></p>
            </section>

            <section class="loae-box loae-box--recorded">
                <p>Recorded:</p>
                <p class="loae-sign-line"><input type="text" class="loae-inline loae-inline--line" value=""></p>
                <p class="loae-sign-role">College Secretary</p>
                <p class="loae-sign-date">Date <input type="text" class="loae-inline loae-inline--date-small" value=""></p>
                <p class="loae-processed-label">Processed in UIS:</p>
                <p class="loae-sign-line"><input type="text" class="loae-inline loae-inline--line" value=""></p>
                <p class="loae-sign-role">Front-Desk Officer</p>
                <p class="loae-sign-date">Date <input type="text" class="loae-inline loae-inline--date-small" value=""></p>
            </section>
        </div>

        <div class="loae-grid loae-grid--four loae-grid--requirements">
            <section class="loae-box loae-box--requirements">
                <p class="loae-box-title">Requirements:</p>
                <p>For Medical Condition/Pregnancy</p>
                <p>* Medical Certificate</p>
                <p>* Clearance from Medical Officer</p>
            </section>
            <section class="loae-box loae-box--requirements">
                <p class="loae-box-title">Requirements:</p>
                <p>For Work</p>
                <p>* Certificate of Employment</p>
                <p>* Clearance from Guidance Counselor</p>
            </section>
            <section class="loae-box loae-box--requirements">
                <p class="loae-box-title">Requirements:</p>
                <p>For Unavailability of Subject</p>
                <p>* Clearance from Reg. Off. College Secretary</p>
            </section>
            <section class="loae-box loae-box--requirements">
                <p class="loae-box-title">Requirements:</p>
                <p>For Financial Constraint/Family Problem</p>
                <p>* Clearance from Guidance Counselor</p>
            </section>
        </div>

        <div class="loae-readmission">
            <p class="loae-readmission__title">PRESENT THIS FORM UPON READMISSION:</p>
            <p>
                Student is eligible for readmission until
                <input type="text" class="loae-inline loae-inline--date-small" value="">
                Semester AY
                <input type="text" class="loae-inline loae-inline--date-small" value="">
                only.
            </p>
        </div>
    </article>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/registrar-loa-enrolled.js') }}?v={{ time() }}"></script>
@endpush
