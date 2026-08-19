<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Copy of Grades{{ $student ? ' – ' . strtoupper($student->name) : '' }}</title>
<style>
*  { box-sizing:border-box; margin:0; padding:0; font-family:'Times New Roman',Times,serif; }
body { background:#f0f0f0; color:#000; }

/* ── toolbar (hidden on print) ── */
.cog-toolbar {
    background:#004d27; color:#fff; padding:10px 24px;
    display:flex; align-items:center; justify-content:space-between;
    gap:12px; position:sticky; top:0; z-index:100; font-family:Arial,sans-serif;
}
.cog-toolbar span { font-size:14px; font-weight:600; }
.cog-toolbar a { color:rgba(255,255,255,.75); font-size:13px; text-decoration:none; }
.cog-toolbar a:hover { color:#fff; }
.cog-toolbar select { font-family:Arial,sans-serif; font-size:12px; padding:3px 6px; border-radius:4px; border:none; }
.cog-print-btn {
    background:#fff; color:#004d27; border:none; border-radius:6px;
    padding:7px 20px; font-size:13px; font-weight:700; cursor:pointer;
}
.cog-print-btn:hover { background:#e2f5ea; }

/* ── page shell (Legal size, matching the source template) ── */
.cog-page {
    width:215.9mm; min-height:355.6mm; margin:20px auto;
    background:#fff; padding:12.7mm 0 19mm; position:relative;
    box-shadow:0 4px 24px rgba(0,0,0,.18);
}
.cog-page + .cog-page { margin-top:0; }

/* ── letterhead space — blank; pre-printed on the physical paper stock ── */
.cog-header-space { height:45mm; }

.cog-body { padding:0 6.35mm; }

/* ── section bar (STUDENT DATA / SCHOLASTIC RECORD) ── */
.cog-section-bar {
    text-align:center; font-family:Arial,'Arial Black',sans-serif; font-weight:900; font-size:11pt;
    border-top:2px solid #000; border-bottom:2px solid #000;
    padding:1.2mm 0; margin:0 0 1mm;
}

/* ── field grid: label / colon / value, row heights match source spreadsheet ── */
.cog-fields { width:100%; border-collapse:collapse; }
.cog-fields td { font-size:11pt; vertical-align:top; padding:0.4mm 0; }
.cog-fields td.cog-label { font-weight:normal; white-space:nowrap; width:1%; padding-right:2mm; }
.cog-fields td.cog-colon { width:1%; padding-right:2mm; font-family:Calibri,Arial,sans-serif; }
.cog-fields td.cog-value input,
.cog-fields td.cog-value textarea {
    width:100%; border:none; border-bottom:1px dotted #999; font-family:'Times New Roman',serif;
    font-size:11pt; font-weight:inherit; padding:0 1mm; background:transparent; resize:none;
}
.cog-fields td.cog-value input:focus,
.cog-fields td.cog-value textarea:focus { outline:none; border-bottom-color:#000; }
.cog-fields tr.cog-row-name td.cog-value input { font-weight:bold; }

/* ── grading system + remarks box ── */
.cog-gs-wrap { display:flex; border:2px solid #000; margin-top:1mm; }
.cog-gs-box { flex:1.35; padding:1mm 2mm; }
.cog-gs-title { text-align:center; font-family:Arial,'Arial Black',sans-serif; font-weight:900; font-size:11pt; border-bottom:2px solid #000; margin:-1mm -2mm 1mm; padding:1.2mm 0; }
.cog-gs-grid { display:grid; grid-template-columns:1fr 1fr; gap:0 6mm; font-size:11pt; }
.cog-gs-grid div { padding:0.3mm 0; }
.cog-gs-codes { font-size:11pt; margin-top:0.5mm; }
.cog-gs-credits { font-size:9pt; margin-top:1.5mm; line-height:1.25; font-family:'Times New Roman',serif; }
.cog-gs-credits b { font-weight:bold; }
.cog-remarks-box { flex:1; border-left:2px solid #000; display:flex; flex-direction:column; }
.cog-remarks-title { text-align:center; font-family:Arial,'Arial Black',sans-serif; font-weight:900; font-size:11pt; border-bottom:2px solid #000; padding:1.2mm 0; }
.cog-remarks-val { flex:1; display:flex; align-items:center; justify-content:center; text-align:center; padding:2mm; }
.cog-remarks-val select {
    font-family:'Times New Roman',serif; font-size:10.5pt; font-weight:bold; text-align:center;
    text-align-last:center; border:none; background:transparent; width:100%;
}
.cog-remarks-val select:focus { outline:none; }

/* ── legal notice ── */
.cog-legal { font-size:9pt; margin:2mm 0; text-align:left; line-height:1.35; }

/* ── signatures ── */
.cog-sig-rule { border-top:2px solid #000; margin-top:2mm; }
.cog-sig-grid { display:grid; grid-template-columns:1fr 1fr; gap:0 10mm; margin-top:1.5mm; }
.cog-sig-cell { text-align:center; }
.cog-sig-label { text-align:left; font-weight:bold; font-size:10pt; margin-bottom:9mm; }
.cog-sig-line { border-top:1px solid #000; margin:0 6mm; }
.cog-sig-name { font-weight:bold; font-size:11pt; padding-top:1mm; }
.cog-sig-title { font-size:11pt; font-style:italic; }
.cog-sig-registrar { text-align:center; margin-top:9mm; }

/* ── footer ── */
.cog-footer { margin-top:6mm; display:flex; align-items:flex-end; justify-content:space-between; gap:6mm; }
.cog-footer-note { font-size:8pt; font-style:italic; font-weight:bold; }
.cog-footer-issued { font-size:8pt; font-family:Calibri,Arial,sans-serif; display:flex; align-items:center; gap:2mm; margin-top:1mm; }
.cog-footer-issued input { font-family:Calibri,Arial,sans-serif; font-size:8pt; border:none; border-bottom:1px dotted #999; background:transparent; }
.cog-footer-page { font-size:8pt; text-align:right; }
.cog-print-timestamp { font-size:8pt; text-align:right; margin-top:2mm; }

/* ── page 2: grade table ── */
.cog-name-line { font-size:12pt; font-weight:bold; margin:2mm 0 1mm; display:flex; align-items:baseline; gap:6px; }
.cog-name-line span { flex:1; border-bottom:1px solid #000; padding-bottom:1px; font-weight:normal; font-size:12pt; }
.cog-tbl { width:100%; border-collapse:collapse; font-size:11pt; margin-top:3mm; border:1px solid #000; }
.cog-tbl th { border:1px solid #000; padding:1.2mm 2mm; text-align:center; font-weight:bold; font-size:11pt; }
.cog-tbl th.cog-th-sub { font-size:9pt; }
.cog-tbl td { border:1px solid #000; padding:0.8mm 2mm; vertical-align:top; }
.cog-tbl td.cog-grade-cell { text-align:center; }
.cog-sem-row td { font-weight:bold; border-left:1px solid #000; border-right:none; border-top:none; border-bottom:none; }
.cog-continuation-footer { margin-top:5mm; font-size:8pt; display:flex; justify-content:space-between; align-items:flex-end; }

/* ── print ── */
@media print {
    body { background:#fff; }
    .cog-toolbar { display:none !important; }
    .cog-page { margin:0; box-shadow:none; width:auto; min-height:0; padding:10mm 0; }
    .cog-tbl tr { page-break-inside:avoid; }
    .cog-page + .cog-page { page-break-before:always; }
    @page { size:legal portrait; margin:10mm; }
}
</style>
</head>
<body>

<div class="cog-toolbar">
    <span>Copy of Grades{{ $student ? ' — ' . strtoupper($student->name) : '' }}</span>
    <div style="display:flex;gap:12px;align-items:center;">
        @if($student)
        <a href="{{ url()->previous() }}">Back to Profile</a>
        @endif
        <button class="cog-print-btn" onclick="window.print()">Print</button>
    </div>
</div>

@php
    $fName = $cogFields['name'] ?? '';
    $fields = [
        ['student_no', 'Student Number', $cogFields['student_no'] ?? ''],
        ['name', 'Name', $cogFields['name'] ?? ''],
        ['address', 'Address', $cogFields['address'] ?? ''],
        ['sex', 'Sex', $cogFields['sex'] ?? ''],
        ['date_of_birth', 'Date of Birth', $cogFields['date_of_birth'] ?? ''],
        ['place_of_birth', 'Place of Birth', $cogFields['place_of_birth'] ?? ''],
        ['date_of_admission', 'Date of Admission', $cogFields['date_of_admission'] ?? ''],
        ['admission_credentials', 'Admission Credentials', $cogFields['admission_credentials'] ?? ''],
        ['degree_course_program', "Degree/Course/ Program", $cogFields['degree_course_program'] ?? ''],
        ['date_of_completion', 'Date of Completion', $cogFields['date_of_completion'] ?? ''],
        ['date_of_graduation', 'Date of Graduation', $cogFields['date_of_graduation'] ?? ''],
        ['resolution_no', 'Resolution No.', $cogFields['resolution_no'] ?? ''],
    ];
    $scholasticFields = [
        ['junior_high_school', 'Junior High School', $cogFields['junior_high_school'] ?? ''],
        ['junior_high_school_year_graduated', 'Year Graduated', $cogFields['junior_high_school_year_graduated'] ?? ''],
        ['senior_high_school', 'Senior High School', $cogFields['senior_high_school'] ?? ''],
        ['senior_high_school_year_graduated', 'Year Graduated', $cogFields['senior_high_school_year_graduated'] ?? ''],
    ];
@endphp

{{-- ═══════════════════════════ PAGE 1 ═══════════════════════════ --}}
<div class="cog-page">
    <div class="cog-header-space"></div>
    <div class="cog-body">

        <div class="cog-section-bar">STUDENT DATA</div>
        <table class="cog-fields">
            @foreach($fields as [$key, $label, $value])
                <tr class="{{ $key === 'name' ? 'cog-row-name' : '' }}">
                    <td class="cog-label">{{ $label }}</td>
                    <td class="cog-colon">:</td>
                    <td class="cog-value"><input type="text" name="cog_{{ $key }}" value="{{ $value }}"></td>
                </tr>
            @endforeach
        </table>

        <div class="cog-section-bar" style="margin-top:2mm;">SCHOLASTIC RECORD</div>
        <table class="cog-fields">
            @foreach($scholasticFields as [$key, $label, $value])
                <tr>
                    <td class="cog-label">{{ $label }}</td>
                    <td class="cog-colon">:</td>
                    <td class="cog-value"><input type="text" name="cog_{{ $key }}" value="{{ $value }}"></td>
                </tr>
            @endforeach
        </table>

        <div class="cog-gs-wrap">
            <div class="cog-gs-box">
                <div class="cog-gs-title">GRADING SYSTEM</div>
                <div class="cog-gs-grid">
                    <div>1.00 = 97.5-100</div><div>2.25 = 82.5-85.4</div>
                    <div>1.25 = 94.5-97.4</div><div>2.50 = 79.5-82.4</div>
                    <div>1.50 = 91.5-94.4</div><div>2.75 = 76.5-79.4</div>
                    <div>1.75 = 88.5-91.4</div><div>3.00 = 74.5-76.4</div>
                    <div>2.00 = 85.5-88.4</div><div>5.00 = 74.4 &amp; below&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;GNA</div>
                </div>
                <div class="cog-gs-codes">
                    INC&nbsp; Incomplete &nbsp;&nbsp; OD&nbsp; Officially Dropped<br>
                    UD&nbsp; Unofficially Dropped &nbsp;&nbsp; NC&nbsp; No Credit &nbsp;&nbsp; GNA&nbsp; Grade Not Available
                </div>
                <div class="cog-gs-credits">
                    <b>Credits:</b> One unit of credit is one hour lecture or recitation or three hours of laboratory work each week for the period of a complete semester. The medium of instruction in this University is English except courses in Filipino and other foreign languages.
                </div>
            </div>
            <div class="cog-remarks-box">
                <div class="cog-remarks-title">REMARKS</div>
                <div class="cog-remarks-val">
                    <select name="cog_remarks" onchange="window.location='{{ url()->current() }}?{{ $student ? 'student_id=' . $student->id . '&' : '' }}remarks='+encodeURIComponent(this.value)">
                        @foreach($remarksOptions as $option)
                            <option value="{{ $option }}" {{ $remarks === $option ? 'selected' : '' }}>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="cog-legal">
            This copy is an exact reproduction of the original transcript on file with the Office of the University Registrar and should be considered as an original copy when signed by the university registrar and impressed with the university seal. Any erasure or alteration on this transcript renders the whole document invalid unless authenticated by the signature of the foregoing official.
        </div>

        <div class="cog-sig-rule"></div>
        <div class="cog-sig-grid">
            <div class="cog-sig-cell">
                <div class="cog-sig-label">Prepared by:</div>
                <div class="cog-sig-line"></div>
                <div class="cog-sig-name">{{ $collegeSecretary->signer_name ?? '' }}</div>
                <div class="cog-sig-title">College Secretary</div>
            </div>
            <div class="cog-sig-cell">
                <div class="cog-sig-label">Checked by:</div>
                <div class="cog-sig-line"></div>
                <div class="cog-sig-name">{{ $assistantRegistrar->signer_name ?? '' }}</div>
                <div class="cog-sig-title">Assistant Registrar</div>
            </div>
        </div>
        <div class="cog-sig-registrar">
            <div class="cog-sig-line" style="margin:0 auto;width:60mm;"></div>
            <div class="cog-sig-name">{{ $registrar->signer_name ?? '' }}</div>
            <div class="cog-sig-title">University Registrar</div>
        </div>

        <div class="cog-footer">
            <div>
                <div class="cog-footer-note">Not Valid Without University Seal</div>
                <div class="cog-footer-issued">
                    Date Issued: <input type="date" name="cog_date_issued" value="{{ now()->toDateString() }}">
                </div>
            </div>
            <div>
                <div class="cog-footer-page">Page 1 of 2</div>
                <div class="cog-print-timestamp">Date &amp; Time Printed: {{ now()->format('n/j/Y g:i A') }}</div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════ PAGE 2 — GRADES ═══════════════════════════ --}}
<div class="cog-page">
    <div class="cog-header-space"></div>
    <div class="cog-body">

        <div class="cog-name-line">NAME <span>{{ strtoupper($fName) }}</span></div>
        <div class="cog-name-line">STUDENT NO. <span>{{ $cogFields['student_no'] ?? '' }}</span></div>

        @if($gradesBySyTerm->isEmpty())
            <p style="text-align:center;color:#888;font-size:10pt;margin:8mm 0;">No grade records on file.</p>
        @else
            <table class="cog-tbl">
                <thead>
                    <tr>
                        <th rowspan="2" style="width:100px;">Course Number</th>
                        <th rowspan="2">Descriptive Title of the Course</th>
                        <th colspan="2">Grades</th>
                        <th rowspan="2" style="width:55px;">Credits</th>
                    </tr>
                    <tr>
                        <th class="cog-th-sub" style="width:70px;">Semestral</th>
                        <th class="cog-th-sub" style="width:80px;">Re-exam Completion</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($gradesBySyTerm as $syTerm => $rows)
                        @php [$sy, $term] = explode('|||', $syTerm); @endphp
                        <tr class="cog-sem-row">
                            <td colspan="5">{{ $sy }}, {{ strtoupper($term) }} SEMESTER</td>
                        </tr>
                        @foreach($rows as $r)
                            @php $grade = $r->final_grade; $inc = $r->inc; @endphp
                            <tr>
                                <td>{{ $r->subject_code }}</td>
                                <td>{{ $r->description ?: $r->equiv_subject_code ?: '—' }}</td>
                                <td class="cog-grade-cell">{{ $inc ? 'INC' : (is_numeric($grade) ? number_format((float) $grade, 2) : ($grade ?: '—')) }}</td>
                                <td class="cog-grade-cell"></td>
                                <td class="cog-grade-cell">{{ is_numeric($r->units) ? number_format((float) $r->units, 2) : '—' }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="cog-continuation-footer">
            <div>
                Prepared by: {{ $collegeSecretary->signer_name ?? 'Registrar Staff' }}<br>
                Checked by: {{ $assistantRegistrar->signer_name ?? 'Assistant Registrar' }}
            </div>
            <div style="text-align:right;">
                Page 2 of 2<br>
                {{ now()->format('n/j/Y g:i A') }}
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'p') { e.preventDefault(); window.print(); }
});
</script>
</body>
</html>
