@extends('layouts.student')

@section('title', 'Appeal For Late Application Of Leave Of Absence - PLP')
@section('page-title', 'FORMS')

@section('content')
@php
    $displayStudentNo = $profile->student_no ?? optional($student)->student_no;
@endphp
<div class="cor-scroll-wrapper acd-page loa-page">
    <div class="acd-canvas">
        <div class="acd-actions">
            <button id="acd-print-btn" type="button" class="btn btn-sm acd-print-btn">Print Form</button>
        </div>

        <div class="cor-container acd-form loa-form">
            <div class="cor-header loa-header">
                <div class="cor-header-left form-logo-left">
                    <img src="{{ asset('img/logobg.png') }}" alt="PLP Logo" class="cor-logo">
                </div>
                <div class="cor-header-center form-header-copy">
                    <p class="loa-gov">City Government of Pasig</p>
                    <p class="loa-school">PAMANTASAN NG LUNGSOD NG PASIG</p>
                    <p class="loa-office">OFFICE OF THE UNIVERSITY REGISTRAR</p>
                    <p class="loa-address">Alkalde Jose St. Kapasigan, Pasig City, Philippines1600</p>
                    <p class="loa-contact">Tel Nos. 8642-8300 / registrar@plpasig.edu.ph</p>
                </div>
                <div class="cor-header-right"></div>
            </div>

            <div class="loa-body">
                <div class="loa-top-row">
                    <p>PLPRO FORM NO. 1H-4</p>
                    <p>Date of Application: {{ now()->format('F d, Y') }}</p>
                </div>
                <h2>APPEAL FOR LATE APPLICATION OF LEAVE OF ABSENCE</h2>

                <p class="loa-text-line"><span class="loa-first-indent">I hereby appeal for the approval of my late application of Leave of Absence for the {{ optional($student)->semester ?? '______ Semester' }} of AY {{ optional($student)->school_year ?? '_________' }}</span><br>to ______ Semester of AY _________ due to:</p>

                <div class="loa-options">
                    <label class="loa-check"><input type="checkbox"> medical condition</label>
                    <label class="loa-check"><input type="checkbox"> financial constraint</label>
                    <label class="loa-check"><input type="checkbox"> unavailability of subject</label>
                    <label class="loa-check"><input type="checkbox"> work</label>
                    <label class="loa-check"><input type="checkbox"> pregnancy</label>
                    <label class="loa-check"><input type="checkbox"> family problem</label>
                    <label class="loa-check"><input type="checkbox"> others _____________________</label>
                </div>

                <p class="loa-text-line">I understand that I shall conform to the following conditions due to my non-compliance to the university requirements prior to my leave of absence:</p>
                <ul>
                    <li>That my readmission will be on the next semester from the date of my application of leave; and</li>
                    <li>That I am not eligible to file leave of absence for the next two (2) years</li>
                </ul>

                <p class="loa-sign">_______________________________</p>
                <p class="loa-sign-sub">Student Signature above printed name</p>
                <p class="loa-student-no">Student No: {{ $displayStudentNo ?? '____________________' }}</p>
                <div class="loa-student-divider"></div>

                <div class="loa-grid">
                    <div class="loa-box">
                        <p><strong>To be assessed by Registrar Personnel:</strong></p>
                        <p>Grades Verification (based on last semester attended AY ______ - ______):</p>
                        <p>TOTAL NO OF SUBJECTS : ______</p>
                        <p>CWA : ______</p>
                        <p>FG/UD : ______subj</p>
                        <p>INC : ______subj</p>
                    </div>
                    <div class="loa-box">
                        <table>
                            <tr>
                                <td>Previously granted LOA</td>
                                <td>
                                    <label class="loa-check"><input type="checkbox"> YES</label>
                                    ____
                                    <label class="loa-check"><input type="checkbox"> NO</label>
                                </td>
                            </tr>
                            <tr><td>First Enrolment in PLP</td><td>____________</td></tr>
                            <tr><td>Last Semester Enrolled</td><td>____________</td></tr>
                            <tr><td>No. of Semesters on AWOL</td><td>____________</td></tr>
                            <tr><td>No. of Years Enrolled in PLP</td><td>____________</td></tr>
                            <tr><td>No. of Remaining Semesters</td><td>____________</td></tr>
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
                        <p class="loa-sign-line">___________________________</p>
                        <p class="loa-sign-row"><span>DSA Director</span><span>Date</span></p>
                    </div>
                    <div class="loa-box">
                        <p><strong>FOR MEDICAL CONDITION/PREGNANCY REASONS ONLY:</strong></p>
                        <p>Student's Health Condition:</p>
                        <p>REMARKS: <label class="loa-check"><input type="checkbox"> LOA NECESSARY</label></p>
                        <p class="loa-medical-check"><label class="loa-check"><input type="checkbox"> LOA NOT NECESSARY</label></p>
                        <p class="loa-medical-check"><label class="loa-check"><input type="checkbox"> FIT TO RETURN TO STUDY</label></p>
                        <p class="loa-medical-check"><label class="loa-check"><input type="checkbox"> ADVISED TO EXTEND LEAVE</label></p>
                        <p class="loa-sign-line">___________________________</p>
                        <p class="loa-sign-row"><span>Medical Officer</span></p>
                    </div>
                    <div class="loa-box">
                        <p><strong>FOR FAMILY PROBLEMS/ WORK/ FINANCIAL CONSTRAINTS REASONS ONLY:</strong></p>
                        <p class="loa-sign-line loa-mt-lg">___________________________</p>
                        <p class="loa-sign-row"><span>Counseled:</span></p>
                        <p class="loa-sign-line">___________________________</p>
                        <p class="loa-sign-row"><span>Guidance Counselor</span></p>
                    </div>
                </div>

                <div class="loa-grid loa-grid-third">
                    <div class="loa-box loa-small-sign"><p>Approved by:</p><p class="loa-sign-line"></p><p>College Dean</p><p>Date ________</p></div>
                    <div class="loa-box loa-small-sign"><p>Noted by:</p><p class="loa-sign-line"></p><p>University Registrar</p><p>Date ________</p></div>
                    <div class="loa-box loa-small-sign"><p>Recorded by:</p><p class="loa-sign-line"></p><p>College Secretary</p><p>Date ________</p></div>
                    <div class="loa-box loa-small-sign"><p>Processed in UIS:</p><p class="loa-sign-line"></p><p>Front-Desk Officer</p><p>Date ________</p></div>
                </div>

                <div class="loa-present-divider"></div>
                <div class="loa-present-box">
                    <p><strong>PRESENT THIS FORM UPON READMISSION:</strong></p>
                    <p>Student is granted approval for readmission which will take effect on and until ________ Semester AY ________ only.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
