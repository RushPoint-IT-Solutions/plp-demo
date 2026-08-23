@extends('layouts.registrar')

@section('title', 'PLP - Application for Leave of Absence (Non-Enrolled)')
@section('page-title', 'APPLICATION FOR LEAVE OF ABSENCE (NON-ENROLLED)')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/loa-enrolled.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/loa-non-enrolled.css') }}?v={{ time() }}">
@endpush

@section('content')
@php
    $selectedStudent = $student;
    $studentName = optional($selectedStudent)->name ?: '';
    $studentNo = optional($selectedStudent)->student_no ?: '';
    $program = optional($selectedStudent)->program ?: optional(optional($selectedStudent)->canonicalCourse)->code ?: optional(optional($selectedStudent)->canonicalCourse)->name ?: '';
@endphp

<div class="loae-page">
    <div class="loae-toolbar d-print-none mb-4">
        <div class="form-row align-items-center">

            <div class="col-auto pr-0 mb-2 mb-md-0">
                <label for="loane-student-search" class="loae-toolbar__label font-weight-bold mb-0">STUDENT:</label>
            </div>

            <div class="col mb-2 mb-md-0">
                <div class="loae-search-wrapper position-relative">
                    <input type="text" id="loane-student-search"
                        class="form-control loae-toolbar__search"
                        placeholder="Search by ID or Name..."
                        autocomplete="off">

                    <input type="hidden" id="loae-student-id" name="student_id" value="{{ $selectedStudentId }}">

                    <div class="loae-search-results d-none position-absolute w-100 bg-white border shadow-sm"
                        id="loane-search-results" style="z-index: 1000; top: 100%;">
                    </div>
                </div>
            </div>

            <div class="col-auto">
                <div class="loae-toolbar__actions">
                    <button type="button" id="loae-print-btn"
                            class="btn btn-success btn-block d-md-inline-block loae-toolbar__button"
                            onclick="window.print()">
                        Print
                    </button>
                </div>
            </div>

        </div>
    </div>

    <div class="loae-a4-stage">
    <article class="loae-sheet a4-wrapper" aria-label="Application for Leave of Absence (Non-Enrolled)">
        <header class="loae-document-head">
            <img src="{{ asset('img/logobg.png') }}" alt="PLP Logo" class="loae-document-logo">
            <div class="loae-document-heading">
                <p class="loae-header-line loae-header-line--city">City Government of Pasig</p>
                <p class="loae-header-line loae-header-line--school">PAMANTASAN NG LUNGSOD NG PASIG</p>
                <p class="loae-header-line loae-header-line--office">OFFICE OF THE UNIVERSITY REGISTRAR</p>
                <p class="loae-header-line loae-header-line--address">Alkalde Jose St. Kapasigan, Pasig City, Philippines 1600</p>
                <p class="loae-header-line loae-header-line--contact">Tel Nos. 628-1013 loc 107 Telefax 628-1015</p>
            </div>
        </header>

        <div class="loae-date-row" style="margin-top: -0.1in;">
            <span class="lne-label">Date of Application:</span>
            <input type="text" class="loae-inline loae-inline--date loae-date-input" value="{{ $applicationDate }}">
        </div>

        <p class="loae-form-no">PLPRO FORM NO. IH-3 Revised 2023</p>
        <h2 class="loae-title">APPLICATION FOR LEAVE OF ABSENCE (NON-ENROLLED)</h2>

        <p class="loae-paragraph loae-paragraph--sentence">
            I,
            <span class="loae-sentence-field"><input type="text" class="loae-inline" value="{{ old('student_name', $studentName) }}"></span>,
            a student of Pamantasan ng Lungsod ng Pasig with student number
            <span class="loae-sentence-field loae-sentence-field--student-no"><input type="text" class="loae-inline" value="{{ old('student_no', $studentNo) }}"></span>
            under the
            <span class="loae-sentence-field"><input type="text" class="loae-inline" value="{{ old('program', $program) }}"></span>
            Program, hereby request to apply for a
            <label class="loae-inline-check"><input type="checkbox"><span class="loae-check-render">leave of absence</span></label>
            <label class="loae-inline-check"><input type="checkbox"><span class="loae-check-render">extension of leave of absence</span></label>
            effective this
            <span class="loae-sentence-field"><input type="text" class="loae-inline" value=""></span>
            sem AY
            <span class="loae-sentence-field"><input type="text" class="loae-inline" value=""></span>
            due to the following reason(s):
        </p>

        <div class="loae-reasons">
            <label class="loae-reason-item"><input type="checkbox"><span class="loae-check-render">medical condition</span></label>
            <label class="loae-reason-item"><input type="checkbox"><span class="loae-check-render">financial constraint</span></label>
            <label class="loae-reason-item"><input type="checkbox"><span class="loae-check-render">unavailability of subject</span></label>
            <label class="loae-reason-item"><input type="checkbox"><span class="loae-check-render">work</span></label>
            <label class="loae-reason-item"><input type="checkbox"><span class="loae-check-render">pregnancy</span></label>
            <label class="loae-reason-item"><input type="checkbox"><span class="loae-check-render">family problem</span></label>
            <label class="loae-reason-item"><input type="checkbox"><span class="loae-check-render">others</span></label>
        </div>
        <span class="lne-reason-elaborate"></span>

        <p class="loae-paragraph loae-paragraph--agreement">
            I hereby agree that should I fail to return to the University to resume my studies at the expiration of my leave of absence,
            and without an extension of such leave of absence approved in writing, I shall be dropped from the rolls of the University.
        </p>

        <div class="loae-signature-block">
            <input type="text" class="loae-inline loae-inline--signature loae-inline--center" value="{{ $studentName }}">
            <p class="loae-caption">Student Signature above printed name</p>
            <p class="loae-student-id-line">Student No: <input type="text" class="loae-inline loae-inline--student-no" value="{{ $studentNo }}"></p>
        </div>

        <div class="loae-divider loae-divider--dashed"></div>

        <div class="loae-grid loae-grid--two lne-grid-top" style="grid-template-columns: 1.05fr 1fr 0.85fr; display: grid;">
            <section class="loae-box">
                <p class="loae-box-title">To be assessed by Registrar Personnel:</p>
                <p><strong>Grades Verification</strong> (based on last semester attended AY
                    <span class="loae-sentence-field"><input type="text" class="loae-inline" style="min-width: 2.5ch;" value=""></span>
                    -
                    <span class="loae-sentence-field"><input type="text" class="loae-inline" style="min-width: 2.5ch;" value=""></span>
                    ):
                </p>
                <div class="lne-kv-row"><span class="lne-label">TOTAL NO OF SUBJECTS</span><span>:</span><input type="text" class="loae-inline"></div>
                <div class="lne-kv-row"><span class="lne-label">CWA</span><span>:</span><input type="text" class="loae-inline"></div>
                <div class="lne-kv-row"><span class="lne-label">FG/UD</span><span>:</span><input type="text" class="loae-inline"><span class="lne-suffix">subj</span></div>
                <div class="lne-kv-row"><span class="lne-label">INC</span><span>:</span><input type="text" class="loae-inline"><span class="lne-suffix">subj</span></div>
            </section>

            <section class="loae-box">
                <table class="lne-assess-table">
                    <tbody>
                        <tr>
                            <td>Previously granted LOA</td>
                            <td>
                                <label class="loae-inline-check"><input type="checkbox"><span class="loae-check-render">YES</span></label>
                                <input type="text" class="loae-inline" style="width: 40px;">
                                <label class="loae-inline-check"><input type="checkbox"><span class="loae-check-render">NO</span></label>
                            </td>
                        </tr>
                        <tr><td>First Enrolment in PLP</td><td><input type="text" class="loae-mini-input"></td></tr>
                        <tr><td>Last Semester Enrolled</td><td><input type="text" class="loae-mini-input"></td></tr>
                        <tr><td>No. of Semesters on AWOL</td><td><input type="text" class="loae-mini-input"></td></tr>
                        <tr><td>No. of Years Enrolled in PLP</td><td><input type="text" class="loae-mini-input"></td></tr>
                        <tr><td>No. of Remaining Semesters</td><td><input type="text" class="loae-mini-input"></td></tr>
                    </tbody>
                </table>
            </section>

            <section class="loae-box">
                <p class="lne-remarks-title">REMARKS:</p>
                <p class="lne-remarks-item lne-remarks-item--strong"><label class="loae-inline-check"><input type="checkbox"><span class="loae-check-render">ELIGIBLE FOR LOA</span></label></p>
                <p class="lne-remarks-item lne-remarks-item--strong"><label class="loae-inline-check"><input type="checkbox"><span class="loae-check-render">NOT ELIGIBLE FOR LOA</span></label></p>
                <p class="lne-remarks-item lne-remarks-item--note"><label class="loae-inline-check"><input type="checkbox"><span class="loae-check-render">NO SUBJECT TO BE ENROLLED DURING AWOL PERIOD</span></label></p>
                <p class="lne-remarks-assessed">Assessed by:</p>
                <p class="loae-sign-role">College Secretary</p>
            </section>
        </div>

        <div class="loae-grid loae-grid--two lne-grid-attest" style="margin-top: -1px;">
            <section class="loae-box">
                <p>This is to attest that the student did not commit any offenses stipulated in the student manual nor make any derogatory act contrary to the university's name, reputation and ideals.</p>
                <p class="loae-sign-line"><input type="text" class="loae-inline loae-inline--line" style="width: 100%;" value=""></p>
                <div class="lne-caption-row">
                    <span>DSA Director</span>
                    <span>Date <input type="text" class="loae-inline loae-inline--date-small" value=""></span>
                </div>
            </section>

            <section class="loae-box">
                <p class="loae-box-title">FOR FAMILY PROBLEMS/ WORK/ FINANCIALCONSTRAINTS REASONS ONLY:</p>
                <p>Counseled:</p>
                <p class="loae-sign-line"><input type="text" class="loae-inline loae-inline--line" style="width: 100%;" value=""></p>
                <p class="loae-sign-role">Guidance Counselor</p>
            </section>
        </div>

        <div class="loae-grid loae-grid--two loae-grid--stacked lne-grid-medical">
            <section class="loae-box">
                <p class="loae-box-title">FOR MEDICAL CONDITION/PREGNANCY REASONS ONLY:</p>
                <p>Student's Health Condition: <input type="text" class="loae-inline loae-inline--line" value=""></p>
                <p>REMARKS: <label class="loae-inline-check"><input type="checkbox"><span class="loae-check-render">LOA NECESSARY</span></label></p>
                <p><label class="loae-inline-check"><input type="checkbox"><span class="loae-check-render">LOA NOT NECESSARY</span></label></p>
                <p><label class="loae-inline-check"><input type="checkbox"><span class="loae-check-render">FIT TO RETURN TO STUDY</span></label></p>
                <p><label class="loae-inline-check"><input type="checkbox"><span class="loae-check-render">ADVISED TO EXTEND LEAVE</span></label></p>
                <p class="loae-sign-line"><input type="text" class="loae-inline loae-inline--line" style="width: 100%;" value=""></p>
                <p class="loae-sign-role">Medical Officer</p>
            </section>

            <section class="loae-box"></section>
        </div>

        <div class="loae-grid loae-grid--four loae-grid--staff" style="margin-top: -1px;">
            <section class="loae-box">
                <p><label class="loae-inline-check"><input type="checkbox"><span class="loae-check-render">LOA Approved</span></label></p>
                <p><label class="loae-inline-check"><input type="checkbox"><span class="loae-check-render">LOA Disapproved</span></label></p>
                <p class="loae-sign-line loae-sign-line--spaced"><input type="text" class="loae-inline loae-inline--line mt-auto-mod" value=""></p>
                <p class="loae-sign-role">College Dean</p>
                <p class="loae-sign-date">Date <input type="text" class="loae-inline loae-inline--date-small" value=""></p>
            </section>

            <section class="loae-box">
                <p>Noted by:</p>
                <p class="loae-sign-line loae-sign-line--spaced"><input type="text" class="loae-inline loae-inline--line mt-auto-mod" value=""></p>
                <p class="loae-sign-role">University Registrar</p>
                <p class="loae-sign-date">Date <input type="text" class="loae-inline loae-inline--date-small" value=""></p>
            </section>

            <section class="loae-box">
                <p>Recorded by:</p>
                <p class="loae-sign-line loae-sign-line--spaced"><input type="text" class="loae-inline loae-inline--line mt-auto-mod" value=""></p>
                <p class="loae-sign-role">College Secretary</p>
                <p class="loae-sign-date">Date <input type="text" class="loae-inline loae-inline--date-small" value=""></p>
            </section>

            <section class="loae-box">
                <p>Processed in UIS:</p>
                <p class="loae-sign-line loae-sign-line--spaced"><input type="text" class="loae-inline loae-inline--line mt-auto-mod" value=""></p>
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

        <div class="loae-readmission" style="margin-top: 14px;">
            <p class="loae-readmission__title">PRESENT THIS FORM UPON READMISSION:</p>
            <p>
                Student is granted approval for readmission which will take effect on and until
                <span class="loae-sentence-field loae-sentence-field--readmission"><input type="text" class="loae-inline" value="{{ old('readmission_from') }}"></span>
                Semester AY
                <span class="loae-sentence-field loae-sentence-field--readmission"><input type="text" class="loae-inline" value="{{ old('readmission_semester_ay') }}"></span>
                only.
            </p>
        </div>
    </article>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/registrar-loa-enrolled.js') }}?v={{ time() }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const allStudents = {!! json_encode($students->map(function($s) { return ['id' => $s->id, 'student_no' => (string) $s->student_no, 'name' => (string) $s->name, 'label' => trim((string) $s->student_no . ' - ' . (string) $s->name)]; })) !!};
        const searchInput = document.getElementById('loane-student-search');
        const resultsContainer = document.getElementById('loane-search-results');
        const studentIdInput = document.getElementById('loae-student-id');

        const selectedId = parseInt(studentIdInput.value) || 0;
        if (selectedId > 0) {
            const selectedStudent = allStudents.find(s => s.id === selectedId);
            if (selectedStudent) {
                searchInput.value = selectedStudent.label;
            }
        }

        searchInput.addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase().trim();
            if (query.length === 0) {
                resultsContainer.classList.add('d-none');
                return;
            }

            const filtered = allStudents.filter(s =>
                String(s.student_no || '').toLowerCase().includes(query) ||
                String(s.name || '').toLowerCase().includes(query)
            ).slice(0, 10);

            if (filtered.length === 0) {
                resultsContainer.innerHTML = '<div class="loae-search-result-item">No students found</div>';
            } else {
                resultsContainer.innerHTML = filtered.map(s =>
                    `<div class="loae-search-result-item" data-student-id="${s.id}" data-student-label="${s.label}">${s.label}</div>`
                ).join('');
            }
            resultsContainer.classList.remove('d-none');
        });

        resultsContainer.addEventListener('click', function(e) {
            const item = e.target.closest('.loae-search-result-item');
            if (item) {
                const studentId = item.getAttribute('data-student-id');
                if (!studentId) {
                    return;
                }
                window.location.href = '{{ url('/registrar/registrar-menu/forms/application-leave-of-absence-non-enrolled') }}/' + studentId;
            }
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.loae-search-wrapper')) {
                resultsContainer.classList.add('d-none');
            }
        });

        document.getElementById('loae-print-btn').addEventListener('click', function() {
            window.print();
        });
    });
</script>
@endpush
