@extends('layouts.registrar')

@section('title', 'PLP - Certificate of Registration (COR)')
@section('page-title', 'CERTIFICATE OF REGISTRATION (COR)')

@push('styles')
<style>
    .cor-page {
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        color: #000;
    }

    .student-topbar,
    .student-topbar *,
    .student-page-header,
    .registrar-header,
    .registrar-header * {
        background: #fff !important;
        color: #000 !important;
        background-image: none !important;
    }

    .cor-toolbar {
        width: min(1120px, 100%);
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        margin-bottom: 14px;
        padding: 12px 14px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
    }

    .cor-toolbar form {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
    }

    .cor-toolbar label {
        font-size: 12px;
        font-weight: 700;
        color: #334155;
        margin: 0;
    }

    .cor-toolbar select {
        max-width: 520px;
        height: 36px;
        font-size: 13px;
    }

    .cor-print-btn {
        height: 36px;
        border: 1px solid #94a3b8;
        background: #fff;
        color: #0f172a;
        border-radius: 6px;
        padding: 0 18px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
    }

    .cor-sheet-wrap {
        width: 100%;
        display: grid;
        place-items: center;
        padding: 8px 0 14px;
        overflow-x: auto;
    }

    .cor-sheet {
        width: 816px;
        height: 1344px;
        background: #fff;
        padding: 205px 34px 18px;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 8px;
        line-height: 1.08;
        color: #000;
        box-shadow: none;
        border: 1px solid #111;
        page-break-inside: avoid;
        overflow: hidden;
        box-sizing: border-box;
    }

    .cor-meta-grid {
        display: grid;
        grid-template-columns: 1.4fr 1.15fr .9fr;
        gap: 20px;
        margin-bottom: 5px;
    }

    .cor-info-line {
        display: grid;
        grid-template-columns: 78px 1fr;
        min-height: 13px;
        align-items: start;
        column-gap: 4px;
    }

    .cor-info-line span:first-child {
        font-weight: 700;
    }

    .cor-info-line strong {
        font-size: 10px;
        letter-spacing: .01em;
    }

    .cor-rule {
        border-top: 1px dashed #000;
        margin: 5px 0 2px;
    }

    .cor-scholarship {
        display: grid;
        grid-template-columns: 92px 1fr;
        align-items: center;
        font-size: 8px;
        margin-bottom: 1px;
    }

    .cor-scholarship strong {
        text-align: left;
        font-size: 9px;
    }

    .cor-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        margin-top: 3px;
        border: 1px solid #000;
        border-left-width: 2px;
    }

    .cor-table th,
    .cor-table td {
        padding: 2px 5px;
        vertical-align: top;
    }

    .cor-table th,
    .cor-fees-table .head td {
        background: #fff !important;
        background-image: none !important;
        color: #000 !important;
    }

    .cor-page .cor-table thead,
    .cor-page .cor-table thead tr,
    .cor-page .cor-table thead th,
    .registrar-body .cor-page .cor-table thead th {
        background: #fff !important;
        background-color: #fff !important;
        background-image: none !important;
        color: #000 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .cor-table thead tr:first-child th {
        border-bottom: 1px solid #000;
        font-size: 11px;
        padding: 3px 0 2px;
        text-align: center;
        letter-spacing: .03em;
    }

    .cor-table thead tr:nth-child(2) th {
        border-bottom: 2px solid #000;
        font-size: 7.5px;
        text-align: left;
    }

    .cor-table td {
        height: 16px;
    }

    .cor-num,
    .cor-table .cor-num,
    .cor-table th.cor-num {
        text-align: right;
    }

    .cor-total-row td {
        border-top: 1px solid #000;
        border-bottom: 2px solid #000;
        font-weight: 700;
        font-size: 9px;
    }

    .cor-lower {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-top: 10px;
        align-items: start;
    }

    .cor-fees {
        border: 2px solid #000;
        min-height: 405px;
        padding: 5px 8px;
    }

    .cor-fees-title {
        text-align: center;
        font-size: 8px;
        font-weight: 800;
        letter-spacing: .18em;
        margin-bottom: 1px;
    }

    .cor-fees-table {
        width: 100%;
        border-collapse: collapse;
    }

    .cor-fees-table td {
        padding: 3px 3px;
    }

    .cor-fees-table .head td {
        border-top: 1px solid #000;
        border-bottom: 1px solid #000;
        font-size: 7.5px;
        font-weight: 800;
    }

    .cor-fees-table .section td {
        border-top: 1px solid #000;
        font-weight: 800;
        padding-top: 5px;
    }

    .cor-fees-table .double td {
        border-top: 1px solid #000;
        border-bottom: 1px solid #000;
        font-weight: 800;
    }

    .cor-indent {
        padding-left: 22px !important;
    }

    .cor-amount {
        width: 92px;
        text-align: right;
        white-space: nowrap;
    }

    .cor-current {
        font-size: 11px;
        font-weight: 900;
    }

    .cor-side {
        padding: 10px 0 0;
        font-size: 8.5px;
    }

    .cor-cert-text {
        text-align: center;
        font-size: 9px;
        font-weight: 700;
        margin: 5px 22px 54px;
    }

    .cor-signatures {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin: 0 8px 18px;
    }

    .cor-signature-name {
        border-bottom: 2px solid #000;
        text-align: center;
        font-size: 9px;
        font-weight: 800;
        min-height: 16px;
        margin: 0;
        white-space: nowrap;
    }

    .cor-signature-role {
        text-align: center;
        font-size: 8px;
        font-style: italic;
        margin: 2px 0 0;
    }

    .cor-stamp {
        width: 250px;
        min-height: 145px;
        border: 1px solid #777;
        margin: 0 auto 12px;
        text-align: center;
        padding: 8px 10px 10px;
    }

    .cor-stamp img {
        width: 48px;
        height: 48px;
        object-fit: contain;
        display: block;
        margin: 0 auto 7px;
    }

    .cor-stamp p {
        margin: 0;
        font-size: 7px;
        line-height: 1.25;
    }

    .cor-stamp strong {
        display: block;
        margin-top: 6px;
        font-size: 12px;
        letter-spacing: .03em;
    }

    .cor-stamp span {
        display: block;
        margin-top: 6px;
        font-size: 8px;
        font-weight: 800;
    }

    .cor-notice-title {
        font-size: 8.5px;
        font-weight: 800;
        text-decoration: none;
        margin: 0 0 12px;
    }

    .cor-notice-body {
        text-align: center;
        font-size: 8.5px;
        font-weight: 700;
        margin: 0 34px;
    }

    .cor-legend {
        margin: 10px 0 0 46px;
        font-size: 7.5px;
        font-weight: 700;
    }

    .cor-legend-title {
        margin: 0 0 8px;
        font-size: 8px;
        font-weight: 900;
    }

    .cor-legend p {
        margin: 3px 0;
    }

    .cor-print-meta {
        display: none;
        gap: 18px;
        justify-content: flex-end;
        margin-top: 8px;
        font-size: 7px;
        color: #000;
    }

    .cor-page .pagination,
    .cor-page .rtp-pagination,
    .cor-page .pf-pagination,
    .cor-page .app-table-pager,
    .cor-page .rtp-nav,
    .cor-page .rtp-list,
    .cor-page .rtp-page-num,
    .cor-page .rtp-page-btn {
        display: none !important;
    }

    @media print {
        @page {
            size: legal portrait;
            margin: 5mm;
        }

        body {
            background: #fff !important;
        }

        .d-print-none,
        .cor-toolbar,
        .plp-sidebar,
        .sidebar-overlay,
        .registrar-header,
        .topbar,
        .footer {
            display: none !important;
        }

        .registrar-main,
        .registrar-content,
        .content {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }

        .cor-sheet-wrap {
            padding: 0 !important;
            overflow: visible !important;
        }

        .cor-sheet {
            width: 203.2mm;
            height: 342.9mm;
            min-height: 0;
            padding: 54mm 7mm 4mm;
            box-shadow: none;
            border: 0;
            overflow: hidden;
            page-break-after: avoid;
            page-break-before: avoid;
            page-break-inside: avoid;
            break-after: avoid;
            break-before: avoid;
            break-inside: avoid;
        }

        .cor-table,
        .cor-lower,
        .cor-fees,
        .cor-side {
            page-break-inside: avoid;
        }
    }
</style>
@endpush

@section('content')
@php
    $studentProfile = optional($student)->profile;
    $formattedEnrollmentDate = optional(optional($student)->created_at)->format('m/d/Y') ?: '-';
    $formattedDatePrinted = now()->format('m/d/Y');
    $formattedTimePrinted = now()->format('g:i:sa');
    $defaultPrintedBy = optional(auth()->user())->name ?: 'Registrar User';

    $formattedSchoolYear = trim((string) optional($student)->school_year);
    if ($formattedSchoolYear === '' && optional($student)->relationLoaded('academicTerm')) {
        $formattedSchoolYear = trim((string) optional(optional($student)->academicTerm)->school_year);
    }

    $formattedSemester = strtoupper(trim((string) optional($student)->semester));
    if ($formattedSemester === '' && optional($student)->relationLoaded('academicTerm')) {
        $formattedSemester = strtoupper(trim((string) optional(optional($student)->academicTerm)->term));
    }

    $semesterLabel = $formattedSemester !== ''
        ? (strpos($formattedSemester, 'SEMESTER') !== false ? $formattedSemester : $formattedSemester . ' SEMESTER')
        : '-';
    $academicYearLabel = $formattedSchoolYear !== '' ? $formattedSchoolYear : '-';
    $schoolYearLabel = $academicYearLabel . ($semesterLabel !== '-' ? ' / ' . $semesterLabel : '');
    $enrolledStampTerm = trim(str_replace(' SEMESTER', ' SEM', $semesterLabel) . ' ' . $academicYearLabel);

    $programText = trim((string) optional($student)->program);
    if ($programText === '' && optional($student)->relationLoaded('canonicalCourse')) {
        $programText = trim((string) (optional(optional($student)->canonicalCourse)->name ?: optional(optional($student)->canonicalCourse)->code));
    }
    $programText = $programText !== '' ? $programText : '-';

    $addressParts = array_filter([
        trim((string) optional($studentProfile)->present_street),
        trim((string) optional($studentProfile)->present_barangay),
        trim((string) optional($studentProfile)->present_municipality),
        trim((string) optional($studentProfile)->present_province),
    ]);
    $addressText = count($addressParts) ? implode(', ', $addressParts) : trim((string) optional($student)->address);
    $addressText = $addressText !== '' ? $addressText : '-';

    $studentDisplayName = strtoupper(trim((string) optional($student)->name));
    $studentDisplayName = $studentDisplayName !== '' ? $studentDisplayName : '-';

    $tuitionUnits = number_format((float) $assessment['tuition_units'], 2);
    $nstpUnits = number_format((float) $assessment['nstp_units'], 2);
    $perUnitRate = number_format((float) $assessment['per_unit_rate'], 2);
    $totalTuitionFee = number_format((float) $assessment['total_tuition_fee'], 2);
    $miscellaneousFee = number_format((float) $assessment['miscellaneous_fee'], 2);
    $laboratoryFee = number_format((float) $assessment['laboratory_fee'], 2);
    $currentAccountValue = (float) $assessment['current_account'];
    $currentAccount = number_format($currentAccountValue, 2);
    $midtermDue = number_format($currentAccountValue / 2, 2);
    $finalDue = number_format($currentAccountValue / 2, 2);
@endphp

<div class="cor-page">
    <div class="cor-toolbar d-print-none">
        <form method="GET" action="{{ route('registrar.registrar-menu.forms.cor.certificate-of-registration') }}">
            <label for="cor-student-id">Student</label>
            <select name="student_id" id="cor-student-id" class="form-select" onchange="this.form.submit()">
                @forelse($students as $optionStudent)
                    <option value="{{ $optionStudent->id }}" {{ (int) $selectedStudentId === (int) $optionStudent->id ? 'selected' : '' }}>
                        {{ $optionStudent->student_no }} - {{ $optionStudent->name }}
                    </option>
                @empty
                    <option value="">No student records found</option>
                @endforelse
            </select>
        </form>

        <button type="button" id="cor-registrar-print" class="cor-print-btn">Print</button>
    </div>

    <div class="cor-sheet-wrap">
        <article class="cor-sheet" aria-label="Certificate of Registration">
            <section class="cor-meta-grid">
                <div>
                    <div class="cor-info-line"><span>Enrollment No:</span><span>{{ optional($student)->registration_no ?: optional($student)->id ?: '-' }}</span></div>
                    <div class="cor-info-line"><span>Student No:</span><span><strong>{{ optional($student)->student_no ?: '-' }}</strong></span></div>
                    <div class="cor-info-line"><span>Student Name:</span><span><strong>{{ $studentDisplayName }}</strong></span></div>
                    <div class="cor-info-line"><span>Address:</span><span>{{ $addressText }}</span></div>
                    <div class="cor-info-line"><span>Course:</span><span>{{ $programText }}</span></div>
                    <div class="cor-info-line"><span>Department:</span><span><strong>{{ optional($student)->college ?: optional($student)->department ?: '-' }}</strong></span></div>
                </div>
                <div>
                    <div class="cor-info-line"><span>Enrollment Date:</span><span>{{ $formattedEnrollmentDate }}</span></div>
                    <div class="cor-info-line"><span>Curriculum:</span><span>{{ optional($student)->curriculum ?: '-' }}</span></div>
                    <div class="cor-info-line"><span>School Year:</span><span>{{ $schoolYearLabel }}</span></div>
                </div>
                <div>
                    <div class="cor-info-line"><span>Year Level:</span><span>{{ optional($student)->year_level ?: '-' }}</span></div>
                    <div class="cor-info-line"><span>Student Type:</span><span>{{ optional($student)->student_type ?: 'Old Student' }}</span></div>
                    <div class="cor-info-line"><span>Adjustment No:</span><span>{{ optional($student)->adjustment_no ?: optional($student)->registration_no ?: '-' }}</span></div>
                </div>
            </section>

            <div class="cor-rule"></div>
            <section class="cor-scholarship">
                <span>Scholarship/Grant:</span>
                <strong>UNIFIED FINANCIAL ASSISTANCE FOR TERTIARY EDUCATION</strong>
            </section>
            <div class="cor-rule"></div>

            <table class="cor-table" data-no-auto-pager="1">
                <colgroup>
                    <col style="width:62px">
                    <col>
                    <col style="width:120px">
                    <col style="width:70px">
                    <col style="width:82px">
                    <col style="width:70px">
                    <col style="width:140px">
                    <col style="width:82px">
                </colgroup>
                <thead>
                    <tr><th colspan="8">CLASS SCHEDULE</th></tr>
                    <tr>
                        <th>SUBJECT NAME</th>
                        <th>SUBJECT DESCRIPTION</th>
                        <th>SECTION</th>
                        <th class="cor-num">UNITS</th>
                        <th>ROOM</th>
                        <th>DAYS</th>
                        <th>TIME</th>
                        <th class="cor-num">PAY UNITS</th>
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
                            <td>{{ $subject->name ?: '-' }}</td>
                            <td>{{ $section !== '' ? $section : '-' }}</td>
                            <td class="cor-num">{{ number_format((float) $subject->units, 2) }}</td>
                            <td>{{ $subject->room ?: '-' }}</td>
                            <td>{{ $subject->days ?: '-' }}</td>
                            <td>{{ $timeText !== '' ? $timeText : '-' }}</td>
                            <td class="cor-num">{{ number_format($payUnits, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" style="text-align:center;padding:18px;">No enrolled subjects found for this student.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="cor-total-row">
                        <td colspan="3" class="cor-num">TOTAL:</td>
                        <td class="cor-num">{{ number_format((float) $totalUnits, 2) }}</td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
            </table>

            <section class="cor-lower">
                <div>
                    <div class="cor-fees">
                        <div class="cor-fees-title">ASSESSMENT OF FEES</div>
                        <table class="cor-fees-table" data-no-auto-pager="1">
                            <tbody>
                                <tr class="head"><td>PARTICULARS</td><td class="cor-amount">AMOUNT</td></tr>
                                <tr class="section"><td>TUITION FEE</td><td class="cor-amount"></td></tr>
                                <tr><td class="cor-indent">TUITION FEE</td><td class="cor-amount">{{ $tuitionUnits }} x {{ $perUnitRate }}</td></tr>
                                <tr><td class="cor-indent">CWTS/ROTC TF</td><td class="cor-amount">{{ $nstpUnits }} x {{ $perUnitRate }}</td></tr>
                                <tr class="double"><td>TOTAL TUITION FEE</td><td class="cor-amount">{{ $totalTuitionFee }}</td></tr>
                                <tr class="section"><td>MISCELLANEOUS FEE</td><td class="cor-amount"></td></tr>
                                <tr><td class="cor-indent">MISCELLANEOUS FEE</td><td class="cor-amount">{{ $miscellaneousFee }}</td></tr>
                                <tr class="double"><td>TOTAL MISCELLANEOUS FEE</td><td class="cor-amount">{{ $miscellaneousFee }}</td></tr>
                                <tr class="section"><td>LABORATORY FEE</td><td class="cor-amount"></td></tr>
                                <tr><td class="cor-indent">LABORATORY FEE</td><td class="cor-amount">{{ $laboratoryFee }}</td></tr>
                                <tr class="double"><td>TOTAL LABORATORY FEE</td><td class="cor-amount">{{ $laboratoryFee }}</td></tr>
                                <tr><td><strong>OLD ACCOUNT</strong></td><td class="cor-amount">-</td></tr>
                                <tr><td><strong>CURRENT ACCOUNT</strong></td><td class="cor-amount cor-current">{{ $currentAccount }}</td></tr>
                                <tr><td class="cor-indent"><strong>CONTRACT / PETITION SUBJECT</strong></td><td class="cor-amount">-</td></tr>
                                <tr><td class="cor-indent"><strong>MIDTERM DUE</strong></td><td class="cor-amount"><strong>{{ $midtermDue }}</strong></td></tr>
                                <tr><td class="cor-indent"><strong>FINAL DUE</strong></td><td class="cor-amount"><strong>{{ $finalDue }}</strong></td></tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="cor-legend">
                        <p class="cor-legend-title">LEGEND</p>
                        <p>* &nbsp;-&nbsp; Added Subject/s</p>
                        <p>** -&nbsp; Officially Dropped Subject/s</p>
                    </div>
                </div>

                <div class="cor-side">
                    <p class="cor-cert-text">This is to certify that the student whose name appears on this document is officially enrolled this term with subject load listed above.</p>

                    <div class="cor-signatures">
                        <div>
                            <p class="cor-signature-name">{{ $studentDisplayName }}</p>
                            <p class="cor-signature-role">STUDENT SIGNATURE</p>
                        </div>
                        <div>
                            <p class="cor-signature-name">FEDERICO G. NUEVA MT</p>
                            <p class="cor-signature-role">UNIVERSITY REGISTRAR</p>
                        </div>
                    </div>

                    <div class="cor-stamp">
                        <img src="{{ asset('img/logobg.png') }}" alt="PLP Logo">
                        <p>PAMANTASAN NG LUNGSOD NG PASIG</p>
                        <p>OFFICE OF THE UNIVERSITY REGISTRAR</p>
                        <strong>OFFICIALLY ENROLLED</strong>
                        <span>{{ $enrolledStampTerm }}</span>
                    </div>

                    <p class="cor-notice-title">Notice to all Students :</p>
                    <p class="cor-notice-body">Present this certificate of registration for any claim or transaction that you engage in within the University.</p>
                </div>
            </section>

            <footer class="cor-print-meta">
                <span>Printed by: <span id="cor-printed-by-value" data-default="{{ $defaultPrintedBy }}">{{ $defaultPrintedBy }}</span></span>
                <span>Time Printed: <span id="cor-time-printed">{{ $formattedTimePrinted }}</span></span>
                <span>Date Printed: <span id="cor-date-printed">{{ $formattedDatePrinted }}</span></span>
            </footer>
        </article>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/registrar-cor.js') }}?v={{ time() }}"></script>
@endpush
