@extends($layout ?? 'layouts.student')

@section('title', $pageTitle ?? 'Appeal For Late Application Of Leave Of Absence - PLP')
@section('page-title', $pageHeading ?? 'FORMS')
@section('body-class', ($isRegistrarView ?? false) ? 'page-registrar-late-loa page-student-forms' : 'page-student-forms')

@section('content')
@php
    $displayStudentNo = $profile->student_no ?? optional($student)->student_no;
@endphp
<div class="cor-scroll-wrapper acd-page loa-page">
    <div class="acd-canvas">
        @if($isRegistrarView ?? false)
        <form method="GET" action="{{ route('registrar.registrar-menu.forms.late-application-leave-of-absence') }}" class="acd-actions d-print-none" aria-label="Select student for late leave application">
            <label for="late-loa-student" class="mb-0 font-weight-bold">Student:</label>
            <select id="late-loa-student" name="student_id" class="form-control form-control-sm" style="max-width: 420px;" onchange="this.form.submit()">
                @foreach($students as $studentOption)
                    <option value="{{ $studentOption->id }}" {{ (int) $selectedStudentId === (int) $studentOption->id ? 'selected' : '' }}>
                        {{ $studentOption->student_no }} — {{ $studentOption->name }}
                    </option>
                @endforeach
            </select>
        </form>
        @endif
        <div class="acd-actions">
            <button id="acd-print-btn" type="button" class="btn btn-sm acd-print-btn" @if($isRegistrarView ?? false) onclick="window.print()" @endif>Print Form</button>
        </div>

        <div class="cor-container acd-form loa-form">
            <div class="loa-body">
                <p style="margin: 0 0 8px; font-weight: 700; text-align: left;">PLPRO FORM NO. 1H-4</p>
                <div class="loa-top-row">
                    <p>Date of Application: <input type="text" class="acd-inline-input acd-inline-input--md no-print-underline" value="{{ now()->format('F d, Y') }}"></p>
                </div>
                <h2>APPEAL FOR LATE APPLICATION OF LEAVE OF ABSENCE</h2>

                <p class="loa-text-line"><span class="loa-first-indent">I hereby appeal for the approval of my late application of Leave of Absence for the <input type="text" class="acd-inline-input acd-inline-input--sm" value="{{ optional($student)->semester ?? '' }}"> of AY <input type="text" class="acd-inline-input acd-inline-input--sm" value="{{ optional($student)->school_year ?? '' }}"></span><br>to <input type="text" class="acd-inline-input acd-inline-input--sm" value=""> Semester of AY <input type="text" class="acd-inline-input acd-inline-input--sm" value=""> due to:</p>

                <div class="loa-options">
                    <label class="loa-check"><input type="checkbox"> medical condition</label>
                    <label class="loa-check"><input type="checkbox"> financial constraint</label>
                    <label class="loa-check"><input type="checkbox"> unavailability of subject</label>
                    <label class="loa-check"><input type="checkbox"> work</label>
                    <label class="loa-check"><input type="checkbox"> pregnancy</label>
                    <label class="loa-check"><input type="checkbox"> family problem</label>
                    <label class="loa-check"><input type="checkbox"> others <input type="text" class="acd-inline-input acd-inline-input--md" value=""></label>
                </div>

                <p class="loa-text-line">I understand that I shall conform to the following conditions due to my non-compliance to the university requirements prior to my leave of absence:</p>
                <ul>
                    <li>That my readmission will be on the next semester from the date of my application of leave; and</li>
                    <li>That I am not eligible to file leave of absence for the next two (2) years</li>
                </ul>

                <p class="loa-sign"><input type="text" class="acd-inline-input acd-inline-input--xl acd-inline-input--center" value=""></p>
                <p class="loa-sign-sub">Student Signature above printed name</p>
                <p class="loa-student-no">Student No: <input type="text" class="acd-inline-input acd-inline-input--md" value="{{ $displayStudentNo ?? '' }}"></p>
                <div class="loa-student-divider"></div>

                <div class="loa-grid">
                    <div class="loa-box">
                        <p><strong>To be assessed by Registrar Personnel:</strong></p>
                        <p>Grades Verification (based on last semester attended AY <input type="text" class="acd-inline-input acd-inline-input--xs" value=""> - <input type="text" class="acd-inline-input acd-inline-input--xs" value="">):</p>
                        <p>TOTAL NO OF SUBJECTS : <input type="text" class="acd-inline-input acd-inline-input--xs" value=""></p>
                        <p>CWA : <input type="text" class="acd-inline-input acd-inline-input--xs" value=""></p>
                        <p>FG/UD : <input type="text" class="acd-inline-input acd-inline-input--xs" value=""> subj</p>
                        <p>INC : <input type="text" class="acd-inline-input acd-inline-input--xs" value=""> subj</p>
                    </div>
                    <div class="loa-box">
                        <table>
                            <tr>
                                <td>Previously granted LOA</td>
                                <td>
                                    <label class="loa-check"><input type="checkbox"> YES</label>
                                    <input type="text" class="acd-inline-input acd-inline-input--xs" value="">
                                    <label class="loa-check"><input type="checkbox"> NO</label>
                                </td>
                            </tr>
                            <tr><td>First Enrolment in PLP</td><td><input type="text" class="acd-inline-input acd-inline-input--sm" value=""></td></tr>
                            <tr><td>Last Semester Enrolled</td><td><input type="text" class="acd-inline-input acd-inline-input--sm" value=""></td></tr>
                            <tr><td>No. of Semesters on AWOL</td><td><input type="text" class="acd-inline-input acd-inline-input--sm" value=""></td></tr>
                            <tr><td>No. of Years Enrolled in PLP</td><td><input type="text" class="acd-inline-input acd-inline-input--sm" value=""></td></tr>
                            <tr><td>No. of Remaining Semesters</td><td><input type="text" class="acd-inline-input acd-inline-input--sm" value=""></td></tr>
                        </table>
                    </div>
                    <div class="loa-box">
                        <p><strong>REMARKS:</strong></p>
                        <p><label class="loa-check"><input type="checkbox"> ELIGIBLE FOR LOA</label></p>
                        <p><label class="loa-check"><input type="checkbox"> NOT ELIGIBLE FOR LOA</label></p>
                        <p class="loa-remark-line"><label class="loa-check"><input type="checkbox"> NO SUBJECT TO BE ENROLLED</label></p>
                        <p class="loa-remark-line loa-remark-cont">DURING AWOL PERIOD</p>
                        <p class="loa-assessed">Assessed by:</p>
                        <p class="loa-assessor">College Secretary</p>
                    </div>
                </div>

                <div class="loa-grid loa-grid-second">
                    <div class="loa-box">
                        <p>This is to attest that the student did not commit any offenses stipulated in the student manual nor make any derogatory act contrary to the university's name, reputation and ideals.</p>
                        <p class="loa-sign-line"><input type="text" class="acd-inline-input acd-inline-input--lg" value=""></p>
                        <p class="loa-sign-row"><span>DSA Director</span><span>Date</span></p>
                    </div>
                    <div class="loa-box">
                        <p><strong>FOR MEDICAL CONDITION/PREGNANCY REASONS ONLY:</strong></p>
                        <p>Student's Health Condition:</p>
                        <p>REMARKS: <label class="loa-check"><input type="checkbox"> LOA NECESSARY</label></p>
                        <p class="loa-medical-check"><label class="loa-check"><input type="checkbox"> LOA NOT NECESSARY</label></p>
                        <p class="loa-medical-check"><label class="loa-check"><input type="checkbox"> FIT TO RETURN TO STUDY</label></p>
                        <p class="loa-medical-check"><label class="loa-check"><input type="checkbox"> ADVISED TO EXTEND LEAVE</label></p>
                        <p class="loa-sign-line"><input type="text" class="acd-inline-input acd-inline-input--lg" value=""></p>
                        <p class="loa-sign-row"><span>Medical Officer</span></p>
                    </div>
                    <div class="loa-box">
                        <p><strong>FOR FAMILY PROBLEMS/ WORK/ FINANCIAL CONSTRAINTS REASONS ONLY:</strong></p>
                        <p class="loa-sign-line loa-mt-lg"><input type="text" class="acd-inline-input acd-inline-input--lg" value=""></p>
                        <p class="loa-sign-row"><span>Counseled:</span></p>
                        <p class="loa-sign-line"><input type="text" class="acd-inline-input acd-inline-input--lg" value=""></p>
                        <p class="loa-sign-row"><span>Guidance Counselor</span></p>
                    </div>
                </div>

                <div class="loa-grid loa-grid-third">
                    <div class="loa-box loa-small-sign"><p>Approved by:</p><p class="loa-sign-line"><input type="text" class="acd-inline-input acd-inline-input--lg"></p><p>College Dean</p><p>Date <input type="text" class="acd-inline-input acd-inline-input--xs no-print-underline" value=""></p></div>
                    <div class="loa-box loa-small-sign"><p>Noted by:</p><p class="loa-sign-line"><input type="text" class="acd-inline-input acd-inline-input--lg"></p><p>University Registrar</p><p>Date <input type="text" class="acd-inline-input acd-inline-input--xs no-print-underline" value=""></p></div>
                    <div class="loa-box loa-small-sign"><p>Recorded by:</p><p class="loa-sign-line"><input type="text" class="acd-inline-input acd-inline-input--lg"></p><p>College Secretary</p><p>Date <input type="text" class="acd-inline-input acd-inline-input--xs no-print-underline" value=""></p></div>
                    <div class="loa-box loa-small-sign"><p>Processed in UIS:</p><p class="loa-sign-line"><input type="text" class="acd-inline-input acd-inline-input--lg"></p><p>Front-Desk Officer</p><p>Date <input type="text" class="acd-inline-input acd-inline-input--xs no-print-underline" value=""></p></div>
                </div>

                <div class="loa-present-divider"></div>
                <div class="loa-present-box">
                    <p><strong>PRESENT THIS FORM UPON READMISSION:</strong></p>
                    <p>Student is granted approval for readmission which will take effect on and until <input type="text" class="acd-inline-input acd-inline-input--xs" value=""> Semester AY <input type="text" class="acd-inline-input acd-inline-input--xs" value=""> only.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
