@extends('layouts.registrar')

@section('title', 'PLP - Certificate of Registration (COR)')
@section('page-title', 'CERTIFICATE OF REGISTRATION (COR)')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/cor-certificate-of-registration.css') }}?v={{ time() }}">
@endpush

@section('content')
@php
    $formattedEnrollmentDate = optional(optional($student)->created_at)->format('m/d/Y');
    $formattedDatePrinted = now()->format('m/d/Y');
    $formattedTimePrinted = now()->format('g:i:sa');
    $defaultPrintedBy = optional(auth()->user())->name ?: 'Registrar User';

    $formattedSchoolYear = trim((string) optional($student)->school_year);
    $formattedSemester = strtoupper(trim((string) optional($student)->semester));
    $schoolYearLabel = $formattedSchoolYear !== ''
        ? $formattedSchoolYear . ($formattedSemester !== '' ? ' / ' . $formattedSemester . ' SEMESTER' : '')
        : '-';

    $studentDisplayName = strtoupper((string) optional($student)->name);
    $studentDisplayName = $studentDisplayName !== '' ? $studentDisplayName : '-';

    $tuitionUnits = number_format((float) $assessment['tuition_units'], 2);
    $nstpUnits = number_format((float) $assessment['nstp_units'], 2);
    $perUnitRate = number_format((float) $assessment['per_unit_rate'], 2);
    $totalTuitionFee = number_format((float) $assessment['total_tuition_fee'], 2);
    $miscellaneousFee = number_format((float) $assessment['miscellaneous_fee'], 2);
    $laboratoryFee = number_format((float) $assessment['laboratory_fee'], 2);
    $currentAccount = number_format((float) $assessment['current_account'], 2);
@endphp

<div class="cor-registrar-page">
    <div class="cor-registrar-toolbar d-print-none">
        <form method="GET" action="{{ route('registrar.registrar-menu.forms.cor.certificate-of-registration') }}" class="cor-registrar-toolbar__form">
            <label for="cor-student-id" class="cor-registrar-toolbar__label">Student</label>
            <select name="student_id" id="cor-student-id" class="form-select cor-registrar-toolbar__select">
                @forelse($students as $optionStudent)
                    <option value="{{ $optionStudent->id }}" {{ (int) $selectedStudentId === (int) $optionStudent->id ? 'selected' : '' }}>
                        {{ $optionStudent->student_no }} - {{ $optionStudent->name }}
                    </option>
                @empty
                    <option value="">No student records found</option>
                @endforelse
            </select>
        </form>

        <div class="cor-registrar-toolbar__actions">
            <button type="button" id="cor-registrar-print" class="btn btn-outline-secondary cor-registrar-toolbar__button">Print</button>
        </div>
    </div>

    <article class="cor-registrar-sheet" aria-label="Certificate of Registration">
        <section class="cor-registrar-student-info">
            <div class="cor-registrar-info-grid">
                <div class="cor-registrar-info-col">
                    <div class="cor-registrar-info-line"><span class="cor-registrar-info-label">Enrollment No:</span><span class="cor-registrar-info-value">{{ optional($student)->registration_no ?: '-' }}</span></div>
                    <div class="cor-registrar-info-line"><span class="cor-registrar-info-label">Student No:</span><span class="cor-registrar-info-value">{{ optional($student)->student_no ?: '-' }}</span></div>
                    <div class="cor-registrar-info-line"><span class="cor-registrar-info-label">Student Name:</span><span class="cor-registrar-info-value cor-registrar-info-value--name">{{ optional($student)->name ?: '-' }}</span></div>
                    <div class="cor-registrar-info-line"><span class="cor-registrar-info-label">Address:</span><span class="cor-registrar-info-value">{{ optional($student)->address ?: '-' }}</span></div>
                    <div class="cor-registrar-info-line"><span class="cor-registrar-info-label">Course:</span><span class="cor-registrar-info-value">{{ optional($student)->program ?: '-' }}</span></div>
                    <div class="cor-registrar-info-line"><span class="cor-registrar-info-label">Department:</span><span class="cor-registrar-info-value">{{ optional($student)->college ?: '-' }}</span></div>
                </div>

                <div class="cor-registrar-info-col">
                    <div class="cor-registrar-info-line"><span class="cor-registrar-info-label">Enrollment Date:</span><span class="cor-registrar-info-value">{{ $formattedEnrollmentDate ?: '-' }}</span></div>
                    <div class="cor-registrar-info-line"><span class="cor-registrar-info-label">Curriculum:</span><span class="cor-registrar-info-value">{{ optional($student)->curriculum ?: '-' }}</span></div>
                    <div class="cor-registrar-info-line"><span class="cor-registrar-info-label">School Year:</span><span class="cor-registrar-info-value">{{ $schoolYearLabel }}</span></div>
                </div>

                <div class="cor-registrar-info-col">
                    <div class="cor-registrar-info-line"><span class="cor-registrar-info-label">Year Level:</span><span class="cor-registrar-info-value">{{ optional($student)->year_level ?: '-' }}</span></div>
                    <div class="cor-registrar-info-line"><span class="cor-registrar-info-label">Student Type:</span><span class="cor-registrar-info-value">{{ optional($student)->student_type ?: 'Old Student' }}</span></div>
                </div>
            </div>

            <div class="cor-registrar-scholarship-line">
                <span class="cor-registrar-info-label">Scholarship/Grant:</span>
                <span class="cor-registrar-info-value cor-registrar-info-value--emphasis">{{ optional($student)->scholarship ?: '-' }}</span>
            </div>
        </section>

        <table class="cor-registrar-table">
            <thead>
                <tr>
                    <th colspan="8" class="cor-registrar-table-title">CLASS SCHEDULE</th>
                </tr>
                <tr>
                    <th>SUBJECT NAME</th>
                    <th>SUBJECT DESCRIPTION</th>
                    <th>SECTION</th>
                    <th>UNITS</th>
                    <th>ROOM</th>
                    <th>DAYS</th>
                    <th>TIME</th>
                    <th>PAY UNITS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subjects as $subject)
                    @php
                        $section = trim((string) $subject->year_section);
                        if ($section === '') {
                            $section = trim((string) optional($student)->program . ' ' . (string) optional($student)->year_level);
                        }

                        $timeText = trim((string) $subject->formatted_time);
                        if ($timeText === '') {
                            $timeText = trim((string) $subject->time_range);
                        }

                        $payUnits = is_numeric($subject->credited_tuition_units)
                            ? (float) $subject->credited_tuition_units
                            : (is_numeric($subject->units) ? (float) $subject->units : 0);
                    @endphp
                    <tr>
                        <td>{{ $subject->code ?: '-' }}</td>
                        <td class="cor-registrar-table__description">{{ $subject->name ?: '-' }}</td>
                        <td>{{ $section !== '' ? $section : '-' }}</td>
                        <td class="cor-registrar-table__numeric">{{ number_format((float) $subject->units, 2) }}</td>
                        <td>{{ $subject->room ?: '-' }}</td>
                        <td>{{ $subject->days ?: '-' }}</td>
                        <td>{{ $timeText !== '' ? $timeText : '-' }}</td>
                        <td class="cor-registrar-table__numeric">{{ number_format($payUnits, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="cor-registrar-table__empty">No enrolled subjects found for this student.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2"></td>
                    <td class="cor-registrar-table__total-label">TOTAL:</td>
                    <td class="cor-registrar-table__total-value">{{ number_format((float) $totalUnits, 2) }}</td>
                    <td colspan="4"></td>
                </tr>
            </tfoot>
        </table>

        <section class="cor-registrar-assessment">
            <div class="cor-registrar-assessment__left">
                <h2 class="cor-registrar-assessment__title">ASSESSMENT OF FEES</h2>
                <table class="cor-registrar-assessment__table">
                    <tbody>
                        <tr class="cor-registrar-assessment__rule"><td><strong>PARTICULARS</strong></td><td class="cor-registrar-assessment__amount"><strong>AMOUNT</strong></td></tr>
                        <tr><td>TUITION FEE</td><td class="cor-registrar-assessment__amount"></td></tr>
                        <tr><td class="cor-registrar-assessment__indent">TUITION FEE</td><td class="cor-registrar-assessment__amount">{{ $tuitionUnits }} x {{ $perUnitRate }}</td></tr>
                        <tr><td class="cor-registrar-assessment__indent">CWTS/ROTC TF</td><td class="cor-registrar-assessment__amount">{{ $nstpUnits }} x {{ $perUnitRate }}</td></tr>
                        <tr class="cor-registrar-assessment__rule cor-registrar-assessment__double"><td>TOTAL TUITION FEE</td><td class="cor-registrar-assessment__amount">{{ $totalTuitionFee }}</td></tr>
                        <tr><td>MISCELLANEOUS FEE</td><td class="cor-registrar-assessment__amount"></td></tr>
                        <tr><td class="cor-registrar-assessment__indent">MISCELLANEOUS FEE</td><td class="cor-registrar-assessment__amount">{{ $miscellaneousFee }}</td></tr>
                        <tr class="cor-registrar-assessment__rule cor-registrar-assessment__double"><td>TOTAL MISCELLANEOUS FEE</td><td class="cor-registrar-assessment__amount">{{ $miscellaneousFee }}</td></tr>
                        <tr><td>LABORATORY FEE</td><td class="cor-registrar-assessment__amount"></td></tr>
                        <tr><td class="cor-registrar-assessment__indent">LABORATORY FEE</td><td class="cor-registrar-assessment__amount">{{ $laboratoryFee }}</td></tr>
                        <tr class="cor-registrar-assessment__rule cor-registrar-assessment__double"><td>TOTAL LABORATORY FEE</td><td class="cor-registrar-assessment__amount">{{ $laboratoryFee }}</td></tr>
                        <tr><td>OLD ACCOUNT</td><td class="cor-registrar-assessment__amount">-</td></tr>
                        <tr><td><strong>CURRENT ACCOUNT</strong></td><td class="cor-registrar-assessment__amount cor-registrar-assessment__current"><strong>{{ $currentAccount }}</strong></td></tr>
                        <tr><td class="cor-registrar-assessment__indent">CONTRACT / PETITION SUBJECT</td><td class="cor-registrar-assessment__amount">-</td></tr>
                        <tr><td class="cor-registrar-assessment__indent">MIDTERM DUE</td><td class="cor-registrar-assessment__amount">-</td></tr>
                        <tr><td class="cor-registrar-assessment__indent">FINAL DUE</td><td class="cor-registrar-assessment__amount">-</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="cor-registrar-assessment__right">
                <p class="cor-registrar-certification">This is to certify that the student whose name appears on this document is officially enrolled this term with subject load listed above.</p>

                <div class="cor-registrar-signatures">
                    <div class="cor-registrar-signature-block">
                        <p class="cor-registrar-signature-name">{{ $studentDisplayName }}</p>
                        <p class="cor-registrar-signature-role">STUDENT SIGNATURE</p>
                    </div>
                    <div class="cor-registrar-signature-block">
                        <p class="cor-registrar-signature-name">Prof. Federico G. Nueva</p>
                        <p class="cor-registrar-signature-role">UNIVERSITY REGISTRAR</p>
                    </div>
                </div>

                <div class="cor-registrar-notice">
                    <p class="cor-registrar-notice__title">Notice to all Students :</p>
                    <p class="cor-registrar-notice__body">Present this certificate of registration for any claim or transaction that you engage in within the University.</p>
                </div>
            </div>
        </section>

        <footer class="cor-registrar-footer">
            <div class="cor-registrar-legend">
                <p class="cor-registrar-legend__title">LEGEND</p>
                <p class="cor-registrar-legend__item">* - Added Subject/s</p>
                <p class="cor-registrar-legend__item">** - Officially Dropped Subject/s</p>
            </div>

            <div class="cor-registrar-printed-meta">
                <span>
                    Printed by:
                    <span
                        id="cor-printed-by-value"
                        class="cor-registrar-printed-meta__editable"
                        contenteditable="true"
                        spellcheck="false"
                        data-default="{{ $defaultPrintedBy }}"
                    >{{ $defaultPrintedBy }}</span>
                </span>
                <span>Time Printed: <span id="cor-time-printed">{{ $formattedTimePrinted }}</span></span>
                <span>Date Printed: <span id="cor-date-printed">{{ $formattedDatePrinted }}</span></span>
            </div>
        </footer>
    </article>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/registrar-cor.js') }}?v={{ time() }}"></script>
@endpush
