@extends('layouts.registrar')

@section('title', 'PLP - Clearance 2')
@section('page-title', 'CLEARANCE 2')

@push('styles')
<style>
    .cl2-page {
        display: flex;
        flex-direction: column;
        align-items: center;
        color: #111;
    }

    .cl2-toolbar {
        width: min(980px, 100%);
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        margin-bottom: 14px;
        padding: 12px 14px;
        background: #fff;
        border: 1px solid #d7dde8;
        border-radius: 8px;
    }

    .cl2-toolbar form {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
    }

    .cl2-toolbar label {
        margin: 0;
        font-size: 12px;
        font-weight: 700;
    }

    .cl2-toolbar select {
        height: 36px;
        max-width: 520px;
        font-size: 13px;
    }

    .cl2-btn {
        height: 36px;
        border: 1px solid #334155;
        background: #fff;
        color: #111827;
        border-radius: 6px;
        padding: 0 16px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }

    .cl2-sheet-wrap {
        width: 100%;
        display: grid;
        place-items: center;
        overflow-x: auto;
        padding: 8px 0 18px;
    }

    .cl2-sheet {
        width: 816px;
        height: 1344px;
        background: #fff;
        border: 1px solid #111;
        padding: 18px 36px 22px;
        font-family: "Times New Roman", Times, serif;
        font-size: 11.5px;
        line-height: 1.08;
        color: #111;
        box-sizing: border-box;
        overflow: hidden;
    }

    .cl2-header {
        display: grid;
        grid-template-columns: 160px 1fr 174px;
        gap: 10px;
        align-items: start;
    }

    .cl2-logo {
        width: 88px;
        height: 88px;
        object-fit: contain;
        justify-self: center;
        margin-top: 5px;
    }

    .cl2-title {
        text-align: center;
        padding-top: 12px;
        font-weight: 700;
    }

    .cl2-school {
        font-size: 20px;
        letter-spacing: .02em;
        white-space: nowrap;
    }

    .cl2-address {
        font-size: 11px;
        font-style: italic;
        font-weight: 400;
        margin-top: 1px;
    }

    .cl2-office {
        font-size: 16px;
        margin-top: 2px;
    }

    .cl2-form-title {
        font-size: 19px;
        letter-spacing: .42em;
        font-weight: 400;
        margin-top: 7px;
    }

    .cl2-batch {
        font-size: 13px;
        margin-top: 1px;
    }

    .cl2-line {
        display: inline-block;
        min-width: 122px;
        border-bottom: 1px solid #111;
        text-align: center;
        font-weight: 700;
        letter-spacing: 0;
    }

    .cl2-photo {
        border: 2px solid #555;
        height: 154px;
        display: grid;
        place-items: center;
        text-align: center;
        color: #aaa;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 15px;
        line-height: 1.18;
        margin-bottom: 4px;
    }

    .cl2-side-meta {
        font-size: 12px;
        line-height: 1.45;
    }

    .cl2-section-title {
        text-align: center;
        font-size: 12.5px;
        font-weight: 700;
        margin: 10px 0 5px;
    }

    .cl2-note {
        font-size: 11px;
        margin: 0 0 7px;
    }

    .cl2-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .cl2-table th,
    .cl2-table td {
        border: 1px solid #bbb;
        padding: 2px 6px;
        vertical-align: middle;
        height: 18px;
    }

    .cl2-table th {
        font-weight: 700;
        text-align: center;
        background: #fff;
    }

    .cl2-label {
        font-weight: 700;
        white-space: nowrap;
    }

    .cl2-center {
        text-align: center;
    }

    .cl2-subnote {
        font-size: 11px;
        margin: -3px 0 3px;
    }

    .cl2-academic {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 12px;
        align-items: start;
        margin-top: 7px;
    }

    .cl2-academic-title {
        text-align: center;
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 2px;
    }

    .cl2-info-row {
        display: grid;
        grid-template-columns: 1fr 1.8fr;
        align-items: center;
        min-height: 19px;
        font-size: 11px;
    }

    .cl2-fill {
        border: 1px solid #c8c8c8;
        min-height: 16px;
        padding: 1px 5px;
    }

    .cl2-heads {
        margin-top: 7px;
        font-size: 11px;
        font-weight: 700;
    }

    .cl2-clearance-grid {
        display: grid;
        grid-template-columns: 1.25fr 1fr 1.35fr;
        gap: 18px;
        margin-top: 7px;
        font-size: 11px;
    }

    .cl2-sign-row {
        display: grid;
        grid-template-columns: 135px 1fr;
        align-items: end;
        min-height: 20px;
    }

    .cl2-sign-line {
        border-bottom: 1px solid #bbb;
        min-height: 15px;
    }

    .cl2-registrar {
        display: grid;
        grid-template-columns: 55px 1fr;
        gap: 8px;
        min-height: 20px;
    }

    .cl2-req-box {
        border: 2px solid #555;
        height: 72px;
        margin-top: 2px;
    }

    .cl2-claim-title {
        font-weight: 700;
        margin: 10px 0 4px 12px;
    }

    .cl2-claim-table {
        font-size: 10px;
    }

    .cl2-claim-table td,
    .cl2-claim-table th {
        height: 16px;
        padding: 1px 4px;
    }

    .cl2-warning {
        margin: 8px 16px 0;
        font-size: 9.5px;
        font-family: Arial, Helvetica, sans-serif;
        font-weight: 700;
        line-height: 1.2;
    }

    .cl2-footer {
        display: grid;
        grid-template-columns: 1.2fr 1fr 1fr;
        gap: 18px;
        align-items: end;
        margin: 14px 40px 0;
        font-size: 13px;
    }

    .cl2-signature {
        border-top: 1px solid #bbb;
        text-align: center;
        padding-top: 2px;
    }

    .cl2-page .pagination,
    .cl2-page .rtp-pagination,
    .cl2-page .pf-pagination,
    .cl2-page .app-table-pager,
    .cl2-page .rtp-nav,
    .cl2-page .rtp-list,
    .cl2-page .rtp-page-num,
    .cl2-page .rtp-page-btn {
        display: none !important;
    }

    @media print {
        @page {
            size: legal portrait;
            margin: 6mm;
        }

        body {
            background: #fff !important;
        }

        .d-print-none,
        .cl2-toolbar,
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

        .cl2-sheet-wrap {
            padding: 0 !important;
            overflow: visible !important;
        }

        .cl2-sheet {
            width: 203.2mm;
            height: 342.9mm;
            min-height: 0;
            border: 0;
            padding: 5mm 8mm 4mm;
            overflow: hidden;
            page-break-inside: avoid;
            break-inside: avoid;
        }
    }
</style>
@endpush

@section('content')
@php
    $prof = optional($student)->profile;
    $rawName = trim((string) optional($student)->name);
    $lastName = trim((string) optional($prof)->last_name);
    $firstName = trim((string) optional($prof)->first_name);
    $middleName = trim((string) optional($prof)->middle_name);

    if ($lastName === '' && $firstName === '' && $rawName !== '') {
        if (strpos($rawName, ',') !== false) {
            [$parsedLast, $parsedRest] = array_pad(explode(',', $rawName, 2), 2, '');
            $lastName = trim($parsedLast);
            $nameParts = preg_split('/\s+/', trim($parsedRest));
            $firstName = trim((string) array_shift($nameParts));
            $middleName = trim(implode(' ', $nameParts));
        } else {
            $nameParts = preg_split('/\s+/', $rawName);
            $firstName = trim((string) array_shift($nameParts));
            $lastName = trim((string) array_pop($nameParts));
            $middleName = trim(implode(' ', $nameParts));
        }
    }

    $addressParts = array_filter([
        trim((string) optional($prof)->present_street),
        trim((string) optional($prof)->present_barangay),
        trim((string) optional($prof)->present_municipality),
        trim((string) optional($prof)->present_province),
    ]);
    $address = count($addressParts) ? implode(', ', $addressParts) : '';

    $course = optional(optional($student)->canonicalCourse)->code
        ?: optional(optional($student)->canonicalCourse)->name
        ?: optional($student)->program
        ?: '';
    $courseName = optional(optional($student)->canonicalCourse)->name ?: $course;
    $term = optional($student)->academicTerm;
    $schoolYear = optional($term)->school_year ?: optional($student)->school_year;
    $semester = optional($term)->term ?: optional($student)->semester;
    $batch = $schoolYear ?: now()->format('Y');
    $printedBy = optional(auth()->user())->name ?: 'Registrar User';
@endphp

<div class="cl2-page">
    <div class="cl2-toolbar d-print-none">
        <form method="GET" action="{{ route('registrar.registrar-menu.forms.clearance-2') }}">
            <label for="student_id">Student</label>
            <select id="student_id" name="student_id" class="form-control" onchange="this.form.submit()">
                <option value="">Select student...</option>
                @foreach($studentOptions as $option)
                    <option value="{{ $option->id }}" {{ $student && (int) $student->id === (int) $option->id ? 'selected' : '' }}>
                        {{ $option->student_no }} - {{ $option->name }}{{ optional($option->canonicalCourse)->code ? ' (' . optional($option->canonicalCourse)->code . ')' : '' }}
                    </option>
                @endforeach
            </select>
        </form>
        <button type="button" class="cl2-btn" onclick="window.print()">Print</button>
    </div>

    <div class="cl2-sheet-wrap">
        <article class="cl2-sheet" aria-label="Clearance 2">
            <div class="cl2-header">
                <img src="{{ asset('img/logobg.png') }}" alt="PLP Logo" class="cl2-logo">

                <div class="cl2-title">
                    <div class="cl2-school">PAMANTASAN NG LUNGSOD NG PASIG</div>
                    <div class="cl2-address">Alcalde Jose Street, Kapasigan, Pasig City</div>
                    <div class="cl2-office">OFFICE OF THE UNIVERSITY REGISTRAR</div>
                    <div class="cl2-form-title">CLEARANCE</div>
                    <div class="cl2-batch">BATCH <span class="cl2-line">{{ $batch }}</span></div>
                </div>

                <div>
                    <div class="cl2-photo">2X2 ID Picture<br>White Background<br>(w/ Toga if Graduates)</div>
                    <div class="cl2-side-meta">
                        <div>Student # : <strong>{{ optional($student)->student_no ?: '' }}</strong></div>
                        <div>Course : <strong>{{ $course }}</strong></div>
                    </div>
                </div>
            </div>

            <div class="cl2-section-title">PERSONAL INFORMATION</div>
            <p class="cl2-note">Any discrepancies between your NSO BC entry you are declaring, please immediately report it at the University Records Office of the Registrar's Office.</p>

            <table class="cl2-table" data-no-auto-pager="1">
                <colgroup>
                    <col style="width: 17%;">
                    <col style="width: 43%;">
                    <col style="width: 40%;">
                </colgroup>
                <thead>
                    <tr>
                        <th></th>
                        <th>NSO BC ENTRY</th>
                        <th>DECLARED</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td class="cl2-label">LAST NAME</td><td></td><td>{{ strtoupper($lastName) }}</td></tr>
                    <tr><td class="cl2-label">FIRST NAME</td><td></td><td>{{ strtoupper($firstName) }}</td></tr>
                    <tr><td class="cl2-label">MIDDLE NAME</td><td></td><td>{{ strtoupper($middleName) }}</td></tr>
                    <tr><td class="cl2-label">SEX</td><td></td><td>{{ strtoupper(optional($prof)->gender ?: optional($student)->sex ?: '') }}</td></tr>
                    <tr><td class="cl2-label">DATE OF BIRTH</td><td></td><td>{{ optional(optional($prof)->date_of_birth)->format('m/d/Y') }}</td></tr>
                    <tr><td class="cl2-label">PLACE OF BIRTH</td><td></td><td>{{ strtoupper(optional($prof)->place_of_birth ?: '') }}</td></tr>
                    <tr><td class="cl2-label">ADDRESS</td><td colspan="2">{{ strtoupper($address) }}</td></tr>
                    <tr><td class="cl2-label">CONTACT NUMBER</td><td colspan="2">{{ optional($prof)->mobile_number ?: '' }}</td></tr>
                    <tr><td class="cl2-label">EMAIL ADDRESS</td><td colspan="2">{{ optional($prof)->student_email ?: '' }}</td></tr>
                </tbody>
            </table>

            <div class="cl2-section-title">EDUCATION INFORMATION</div>
            <div class="cl2-subnote">(Kindly write the last school attended)</div>
            <table class="cl2-table" data-no-auto-pager="1">
                <colgroup>
                    <col style="width: 16%;">
                    <col style="width: 56%;">
                    <col style="width: 28%;">
                </colgroup>
                <thead>
                    <tr>
                        <th></th>
                        <th>NAME OF SCHOOL</th>
                        <th>YEAR GRADUATED</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td class="cl2-label">ELEMENTARY</td><td></td><td></td></tr>
                    <tr><td class="cl2-label">HIGH SCHOOL</td><td>{{ strtoupper(optional($prof)->junior_school ?: optional($prof)->senior_school ?: '') }}</td><td></td></tr>
                    <tr><td class="cl2-label">COLLEGE</td><td>{{ $courseName ? 'PAMANTASAN NG LUNGSOD NG PASIG - ' . strtoupper($courseName) : 'PAMANTASAN NG LUNGSOD NG PASIG' }}</td><td></td></tr>
                    <tr><td class="cl2-label">VOCATIONAL</td><td></td><td></td></tr>
                </tbody>
            </table>

            <div class="cl2-academic">
                <div>
                    <div class="cl2-info-row"><div>TRANSFERRED FROM (NAME OF SCHOOL):</div><div class="cl2-fill"></div></div>
                    <div class="cl2-info-row"><div>COURSE FROM PREVIOUS SCHOOL:</div><div class="cl2-fill"></div></div>
                    <div class="cl2-info-row"><div>SHIFTED FROM COURSE:</div><div class="cl2-fill"></div></div>
                    <div class="cl2-info-row"><div>YEAR SHIFTED:</div><div class="cl2-fill"></div></div>
                </div>
                <div>
                    <div class="cl2-academic-title">PLP ACADEMIC INFORMATION</div>
                    <div class="cl2-info-row"><div>COURSE:</div><div class="cl2-fill">{{ $courseName }}</div></div>
                    <div class="cl2-info-row"><div>YEAR LEVEL:</div><div class="cl2-fill">{{ optional(optional($student)->yearBlock)->label ?: optional($student)->year_level }}</div></div>
                    <div class="cl2-info-row"><div>SCHOOL YEAR:</div><div class="cl2-fill">{{ $schoolYear }}</div></div>
                    <div class="cl2-info-row"><div>SEMESTER:</div><div class="cl2-fill">{{ $semester }}</div></div>
                </div>
            </div>

            <div class="cl2-heads">ATTENTION HEADS: Please affix your signature to affirm that the the student has no remaining accountabilities to your department</div>

            <div class="cl2-clearance-grid">
                <div>
                    <div class="cl2-sign-row"><span>( 1 ) Library</span><span class="cl2-sign-line"></span></div>
                    <div class="cl2-sign-row"><span>( 2 ) College Dean</span><span class="cl2-sign-line"></span></div>
                    <div class="cl2-sign-row"><span>( 3 ) DSA</span><span class="cl2-sign-line"></span></div>
                    <div class="cl2-sign-row"><span>( 4 ) Student Council</span><span class="cl2-sign-line"></span></div>
                    <div class="cl2-sign-row"><span>( 5 ) Finance Office</span><span class="cl2-sign-line"></span></div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;font-size:10px;margin-left:106px;">
                        <span>Out Bal.</span><span>Grad Fee</span>
                    </div>
                    <div class="cl2-sign-row"><span>( 6 ) MIS</span><span class="cl2-sign-line"></span></div>
                </div>
                <div>
                    <div style="font-weight:700;margin-bottom:6px;">( 7 ) Registrar's Office</div>
                    <div class="cl2-registrar"><span></span><span class="cl2-sign-line cl2-center">Record Officer</span></div>
                    <div class="cl2-registrar"><span></span><span class="cl2-sign-line cl2-center">Credit Evaluator</span></div>
                    <div class="cl2-registrar"><span></span><span class="cl2-sign-line cl2-center">University Registrar</span></div>
                </div>
                <div>
                    <div class="cl2-center" style="font-weight:700;">REQUIREMENTS TO BE SUBMITTED</div>
                    <div class="cl2-center">Present Original and two Photocopies</div>
                    <div class="cl2-req-box"></div>
                </div>
            </div>

            <div class="cl2-claim-title">CLAIM SLIP</div>
            <table class="cl2-table cl2-claim-table" data-no-auto-pager="1">
                <colgroup>
                    <col style="width: 28%;">
                    <col style="width: 12%;">
                    <col style="width: 12%;">
                    <col style="width: 12%;">
                    <col style="width: 12%;">
                    <col style="width: 12%;">
                    <col style="width: 12%;">
                </colgroup>
                <thead>
                    <tr>
                        <th>Documents</th>
                        <th>Date Request</th>
                        <th>Date Due</th>
                        <th>Amount</th>
                        <th>OR No.</th>
                        <th>Date Rel.</th>
                        <th>Received By</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach([
                        '( ) Transcript of Records ( ) Copy of Grade',
                        'Purpose: ( ) Employment',
                        '( ) Evaluation',
                        '( ) Board Examination',
                        '( ) Travel',
                        '( ) Transfer Name of School',
                        '( ) Others ____________',
                        '( ) Permanent Record',
                        '( ) Diploma',
                        '( ) Honorable Dismissal',
                        '( ) Certificate',
                        '( ) College Ranking',
                        '( ) Honor',
                        '( ) General Weighted Average',
                        '( ) Certificate of Graduation',
                        '( ) Other Scholastic Record',
                    ] as $document)
                        <tr>
                            <td>{{ $document }}</td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="cl2-warning">
                NO DOCUMENT WILL BE RELEASED TO A THIRD PARTY UNLESS AN AUTHORIZATION LETTER, ID OF THE AUTHORIZED PERSON &amp; SCHOOL ID OF THE STUDENT.<br>
                NO DOCUMENT WILL BE RELEASED UNLESS ALL LACKING ENTRANCE CREDENTIAL AS INDICATED IN SECTION 6 OF THIS FORM HAS BEEN FULLY COMPLIED WITH
            </div>

            <div class="cl2-footer">
                <div style="display:grid;grid-template-columns:90px 1fr;gap:10px;align-items:end;">
                    <strong>CONFORME:</strong>
                    <div class="cl2-signature">Student's Signature</div>
                </div>
                <div><strong>Printed By:</strong> {{ $printedBy }}</div>
                <div><strong>Printed Date:</strong> {{ now()->format('m/d/Y') }}</div>
            </div>
        </article>
    </div>
</div>
@endsection
