<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Transcript of Records – {{ strtoupper($student->name) }}</title>
<style>
*  { box-sizing:border-box; margin:0; padding:0; font-family:'Times New Roman',Times,serif; }
body { background:#f0f0f0; color:#000; }

/* ── toolbar (hidden on print) ── */
.tor-toolbar {
    background:#004d27; color:#fff; padding:10px 24px;
    display:flex; align-items:center; justify-content:space-between;
    gap:12px; position:sticky; top:0; z-index:100;
}
.tor-toolbar span { font-size:14px; font-weight:600; font-family:Arial,sans-serif; }
.tor-toolbar a  { color:rgba(255,255,255,.75); font-size:13px; text-decoration:none; font-family:Arial,sans-serif; }
.tor-toolbar a:hover { color:#fff; }
.tor-toolbar select { font-family:Arial,sans-serif; font-size:12px; padding:3px 6px; border-radius:4px; border:none; }
.tor-print-btn {
    background:#fff; color:#004d27; border:none; border-radius:6px;
    padding:7px 20px; font-size:13px; font-weight:700; cursor:pointer; font-family:Arial,sans-serif;
}
.tor-print-btn:hover { background:#e2f5ea; }

/* ── page shell (Legal size, matching the source template) ── */
.tor-page {
    width:215.9mm; min-height:355.6mm; margin:20px auto;
    background:#fff; padding:12.7mm 0 19mm; position:relative;
    box-shadow:0 4px 24px rgba(0,0,0,.18);
}
.tor-page + .tor-page { margin-top:0; }

/* ── letterhead space — blank; pre-printed on the physical paper stock ── */
.tor-header-space { height:45mm; }

.tor-body { padding:0 6.35mm; }

/* ── section bar (STUDENT DATA / SCHOLASTIC RECORD / GRADING SYSTEM / REMARKS) ── */
.tor-section-bar {
    text-align:center; font-family:Arial,'Arial Black',sans-serif; font-weight:900; font-size:11pt;
    border-top:2px solid #000; border-bottom:2px solid #000;
    padding:1.2mm 0; margin:0 0 1mm;
}

/* ── student data / scholastic record: with an optional photo column ── */
.tor-sd-wrap { display:flex; gap:5mm; align-items:flex-start; }
.tor-sd-fields-col { flex:1; min-width:0; }
.tor-sd-photo { width:38mm; flex-shrink:0; text-align:center; padding-top:2mm; }
.tor-sd-photo-box {
    width:38mm; height:44mm; border:1.5px solid #000; display:flex;
    align-items:center; justify-content:center; overflow:hidden; background:#fafafa; margin:0 auto;
}
.tor-sd-photo-box img { width:100%; height:100%; object-fit:cover; }
.tor-sd-photo-caption { font-size:9pt; font-style:italic; margin-top:2mm; line-height:1.3; }

/* ── field grid: label / colon / value, row heights match source spreadsheet ── */
.tor-fields { width:100%; border-collapse:collapse; }
.tor-fields td { font-size:11pt; vertical-align:top; padding:0.4mm 0; }
.tor-fields td.tor-label { font-weight:normal; white-space:nowrap; width:1%; padding-right:2mm; }
.tor-fields td.tor-colon { width:1%; padding-right:2mm; font-family:Calibri,Arial,sans-serif; }
.tor-fields td.tor-value input {
    width:100%; border:none; border-bottom:1px dotted #999; font-family:'Times New Roman',serif;
    font-size:11pt; font-weight:inherit; padding:0 1mm; background:transparent;
}
.tor-fields td.tor-value input:focus { outline:none; border-bottom-color:#000; }
.tor-fields tr.tor-row-name td.tor-value input { font-weight:bold; }

/* ── grading system + remarks box ── */
.tor-gs-wrap { display:flex; border:2px solid #000; margin-top:1mm; }
.tor-gs-box { flex:1.35; padding:1mm 2mm; }
.tor-gs-title { text-align:center; font-family:Arial,'Arial Black',sans-serif; font-weight:900; font-size:11pt; border-bottom:2px solid #000; margin:-1mm -2mm 1mm; padding:1.2mm 0; }
.tor-gs-grid { display:grid; grid-template-columns:1fr 1fr; gap:0 6mm; font-size:11pt; }
.tor-gs-grid div { padding:0.3mm 0; }
.tor-gs-codes { font-size:11pt; margin-top:0.5mm; }
.tor-gs-credits { font-size:9pt; margin-top:1.5mm; line-height:1.25; font-family:'Times New Roman',serif; }
.tor-gs-credits b { font-weight:bold; }
.tor-remarks-box { flex:1; border-left:2px solid #000; display:flex; flex-direction:column; }
.tor-remarks-title { text-align:center; font-family:Arial,'Arial Black',sans-serif; font-weight:900; font-size:11pt; border-bottom:2px solid #000; padding:1.2mm 0; }
.tor-remarks-val { flex:1; display:flex; align-items:center; justify-content:center; text-align:center; padding:2mm; }
.tor-remarks-val select {
    font-family:'Times New Roman',serif; font-size:10.5pt; font-weight:bold; text-align:center;
    text-align-last:center; border:none; background:transparent; width:100%;
}
.tor-remarks-val select:focus { outline:none; }

/* ── legal notice ── */
.tor-legal { font-size:9pt; margin:2mm 0; text-align:left; line-height:1.35; }

/* ── signatures: bordered Prepared-by/Checked-by box, then Certified True and Correct below ── */
.tor-sig-box { border:2px solid #000; display:grid; grid-template-columns:1fr 1fr; margin-top:2mm; }
.tor-sig-cell { padding:2mm 3mm 4mm; text-align:center; }
.tor-sig-cell + .tor-sig-cell { border-left:2px solid #000; }
.tor-sig-label { text-align:left; font-weight:bold; font-size:10pt; margin-bottom:9mm; }
.tor-sig-name { font-weight:bold; font-size:11pt; }
.tor-sig-title { font-size:11pt; font-style:italic; }
.tor-cert-block { border-top:2px solid #000; margin-top:0; padding:2mm 3mm 4mm; text-align:center; }
.tor-cert-label { text-align:center; font-weight:bold; font-size:10pt; margin-bottom:9mm; }

/* ── footer ── */
.tor-footer { margin-top:6mm; display:flex; align-items:flex-end; justify-content:space-between; gap:6mm; }
.tor-footer-note { font-size:8pt; font-style:italic; font-weight:bold; text-align:center; }
.tor-footer-issued { font-size:8pt; font-family:Calibri,Arial,sans-serif; display:flex; align-items:center; gap:2mm; margin-top:1mm; justify-content:center; }
.tor-footer-issued input { font-family:Calibri,Arial,sans-serif; font-size:8pt; border:none; border-bottom:1px dotted #999; background:transparent; }
.tor-footer-page { font-size:8pt; text-align:right; }
.tor-print-timestamp { font-size:8pt; text-align:right; margin-top:2mm; }

/* ── page 2: grade table ── */
.tor-name-line { font-size:12pt; font-weight:bold; margin:2mm 0 1mm; display:flex; align-items:baseline; gap:6px; }
.tor-name-line span { flex:1; border-bottom:1px solid #000; padding-bottom:1px; font-weight:normal; font-size:12pt; }
.tor-tbl { width:100%; border-collapse:collapse; font-size:11pt; margin-top:3mm; border:1px solid #000; }
.tor-tbl th { border:1px solid #000; padding:1.2mm 2mm; text-align:center; font-weight:bold; font-size:11pt; }
.tor-tbl th.tor-th-sub { font-size:9pt; }
.tor-tbl td { border:1px solid #000; padding:0.8mm 2mm; vertical-align:top; }
.tor-tbl td.grade-cell { text-align:center; }
.tor-sem-row td { font-weight:bold; border-left:1px solid #000; border-right:none; border-top:none; border-bottom:none; }
.tor-continuation-footer { margin-top:5mm; font-size:8pt; display:flex; justify-content:space-between; align-items:flex-end; }

/* ── print ── */
@media print {
    body { background:#fff; }
    .tor-toolbar { display:none !important; }
    .tor-page { margin:0; box-shadow:none; width:auto; min-height:0; padding:10mm 0; }
    .tor-tbl tr { page-break-inside:avoid; }
    .tor-page + .tor-page { page-break-before:always; }
    @page { size:legal portrait; margin:10mm; }
}
</style>
</head>
<body>

<div class="tor-toolbar">
    <span>Transcript of Records — {{ strtoupper($student->name) }}</span>
    <div style="display:flex;gap:12px;align-items:center;">
        <label style="font-size:13px;font-family:Arial,sans-serif;display:flex;gap:6px;align-items:center;">
            Purpose:
            <select onchange="window.location='{{ url()->current() }}?purpose='+encodeURIComponent(this.value)">
                @foreach($purposeOptions as $option)
                    <option value="{{ $option }}" {{ $purpose === $option ? 'selected' : '' }}>{{ $option }}</option>
                @endforeach
            </select>
        </label>
        <a href="{{ url()->previous() }}">Back to Profile</a>
        <button class="tor-print-btn" onclick="window.print()">Print</button>
    </div>
</div>

@php
    $prof = $student->profile;
    $photoUrl = ($prof && $prof->profile_photo_path && file_exists(public_path($prof->profile_photo_path))) ? asset($prof->profile_photo_path) : null;
    $fields = [
        ['student_no', 'Student Number', $torFields['student_no'] ?? ''],
        ['name', 'Name', $torFields['name'] ?? ''],
        ['address', 'Address', $torFields['address'] ?? ''],
        ['sex', 'Sex', $torFields['sex'] ?? ''],
        ['date_of_birth', 'Date of Birth', $torFields['date_of_birth'] ?? ''],
        ['place_of_birth', 'Place of Birth', $torFields['place_of_birth'] ?? ''],
        ['date_of_admission', 'Date of Admission', $torFields['date_of_admission'] ?? ''],
        ['admission_credentials', 'Admission Credentials', $torFields['admission_credentials'] ?? ''],
        ['degree_course_program', 'Degree/Course/ Program', $torFields['degree_course_program'] ?? ''],
        ['date_of_completion', 'Date of Completion', $torFields['date_of_completion'] ?? ''],
        ['date_of_graduation', 'Date of Graduation', $torFields['date_of_graduation'] ?? ''],
        ['resolution_no', 'Resolution No.', $torFields['resolution_no'] ?? ''],
    ];
@endphp

{{-- ═══════════════════════════ PAGE 1 ═══════════════════════════ --}}
<div class="tor-page">
    <div class="tor-header-space"></div>
    <div class="tor-body">

        <div class="tor-section-bar">STUDENT DATA</div>
        <div class="tor-sd-wrap">
            <div class="tor-sd-fields-col">
                <table class="tor-fields">
                    @foreach($fields as [$key, $label, $value])
                        <tr class="{{ $key === 'name' ? 'tor-row-name' : '' }}">
                            <td class="tor-label">{{ $label }}</td>
                            <td class="tor-colon">:</td>
                            <td class="tor-value"><input type="text" name="tor_{{ $key }}" value="{{ $value }}"></td>
                        </tr>
                    @endforeach
                </table>
            </div>
            <div class="tor-sd-photo">
                <div class="tor-sd-photo-box">
                    @if($photoUrl)
                        <img src="{{ $photoUrl }}" alt="Student photo">
                    @endif
                </div>
                <div class="tor-sd-photo-caption">Not Valid Without University Seal and Student's Picture</div>
            </div>
        </div>

        <div class="tor-section-bar" style="margin-top:2mm;">SCHOLASTIC RECORD</div>
        <table class="tor-fields">
            @foreach($scholasticFields as [$label, $value])
                <tr>
                    <td class="tor-label">{{ $label }}</td>
                    <td class="tor-colon">:</td>
                    <td class="tor-value"><input type="text" value="{{ $value }}"></td>
                </tr>
            @endforeach
        </table>

        <div class="tor-gs-wrap">
            <div class="tor-gs-box">
                <div class="tor-gs-title">GRADING SYSTEM</div>
                <div class="tor-gs-grid">
                    <div>1.00 = 97.5-100</div><div>2.25 = 82.5-85.4</div>
                    <div>1.25 = 94.5-97.4</div><div>2.50 = 79.5-82.4</div>
                    <div>1.50 = 91.5-94.4</div><div>2.75 = 76.5-79.4</div>
                    <div>1.75 = 88.5-91.4</div><div>3.00 = 74.5-76.4</div>
                    <div>2.00 = 85.5-88.4</div><div>5.00 = 74.4 &amp; below&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;GNA</div>
                </div>
                <div class="tor-gs-codes">
                    INC&nbsp; Incomplete &nbsp;&nbsp; OD&nbsp; Officially Dropped<br>
                    UD&nbsp; Unofficially Dropped &nbsp;&nbsp; NC&nbsp; No Credit &nbsp;&nbsp; GNA&nbsp; Grade Not Available
                </div>
                <div class="tor-gs-credits">
                    <b>Credits:</b> One unit of credit is one hour lecture or recitation or three hours of laboratory work each week for the period of a complete semester. The medium of instruction in this University is English except courses in Filipino and other foreign languages.
                </div>
            </div>
            <div class="tor-remarks-box">
                <div class="tor-remarks-title">REMARKS</div>
                <div class="tor-remarks-val">
                    <select onchange="window.location='{{ url()->current() }}?purpose='+encodeURIComponent(this.value)">
                        @foreach($purposeOptions as $option)
                            <option value="{{ $option }}" {{ $purpose === $option ? 'selected' : '' }}>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="tor-legal">
            This copy is an exact reproduction of the original transcript on file with the Office of the University Registrar and should be considered as an original copy when signed by the university registrar and impressed with the university seal. Any erasure or alteration on this transcript renders the whole document invalid unless authenticated by the signature of the foregoing official.
        </div>

        <div class="tor-sig-box">
            <div class="tor-sig-cell">
                <div class="tor-sig-label">Prepared by:</div>
                <div class="tor-sig-name">{{ $collegeSecretary->signer_name ?? '' }}</div>
                <div class="tor-sig-title">College Secretary</div>
            </div>
            <div class="tor-sig-cell">
                <div class="tor-sig-label">Checked by:</div>
                <div class="tor-sig-name">{{ $assistantRegistrar->signer_name ?? '' }}</div>
                <div class="tor-sig-title">Assistant Registrar</div>
            </div>
        </div>
        <div class="tor-cert-block">
            <div class="tor-cert-label">Certified True and Correct:</div>
            <div class="tor-sig-name">{{ $registrar->signer_name ?? '' }}</div>
            <div class="tor-sig-title">University Registrar</div>
        </div>

        <div class="tor-footer">
            <div style="flex:1;">
                <div class="tor-footer-note">Not Valid Without University Seal</div>
                <div class="tor-footer-issued">
                    Date Issued: <input type="date" value="{{ now()->toDateString() }}">
                </div>
            </div>
            <div>
                <div class="tor-footer-page">Page 1 of 2</div>
                <div class="tor-print-timestamp">Date &amp; Time Printed: {{ now()->format('n/j/Y g:i A') }}</div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════ PAGE 2 — GRADES ═══════════════════════════ --}}
<div class="tor-page">
    <div class="tor-header-space"></div>
    <div class="tor-body">

        <div class="tor-name-line">NAME <span>{{ strtoupper($torFields['name'] ?? '') }}</span></div>
        <div class="tor-name-line">STUDENT NO. <span>{{ $torFields['student_no'] ?? '' }}</span></div>

        @if($gradesBySyTerm->isEmpty())
            <p style="text-align:center;color:#888;font-size:10pt;margin:8mm 0;">No grade records on file.</p>
        @else
            <table class="tor-tbl">
                <thead>
                    <tr>
                        <th rowspan="2" style="width:100px;">Course Number</th>
                        <th rowspan="2">Descriptive Title of the Course</th>
                        <th colspan="2">Grades</th>
                        <th rowspan="2" style="width:55px;">Credits</th>
                    </tr>
                    <tr>
                        <th class="tor-th-sub" style="width:70px;">Semestral</th>
                        <th class="tor-th-sub" style="width:80px;">Re-exam Completion</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($gradesBySyTerm as $syTerm => $rows)
                        @php [$sy, $term] = explode('|||', $syTerm); @endphp
                        <tr class="tor-sem-row">
                            <td colspan="5">{{ $sy }}, {{ strtoupper($term) }} SEMESTER</td>
                        </tr>
                        @foreach($rows as $r)
                            @php $grade = $r->final_grade; $inc = $r->inc; @endphp
                            <tr>
                                <td>{{ $r->subject_code }}</td>
                                <td>{{ $r->description ?: $r->equiv_subject_code ?: '—' }}</td>
                                <td class="grade-cell">{{ $inc ? 'INC' : (is_numeric($grade) ? number_format((float) $grade, 2) : ($grade ?: '—')) }}</td>
                                <td class="grade-cell"></td>
                                <td class="grade-cell">{{ is_numeric($r->units) ? number_format((float) $r->units, 2) : '—' }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- GWA Summary --}}
        <div style="margin-top:5mm;display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
            <div style="border:2px solid #000;padding:2mm 4mm;display:inline-flex;gap:8mm;align-items:center;font-size:9.5pt;">
                <div>
                    <div style="font-size:8pt;margin-bottom:1mm;">GENERAL WEIGHTED AVERAGE</div>
                    <div style="font-size:13pt;font-weight:700;">{{ $gwa !== null ? number_format($gwa, 4) : 'N/A' }}</div>
                </div>
                <div style="border-left:1px solid #999;padding-left:6mm;">
                    <div style="font-size:8pt;margin-bottom:1mm;">UNITS EARNED</div>
                    <div style="font-size:13pt;font-weight:700;">{{ number_format($totalUnitsEarned, 1) }}</div>
                </div>
            </div>
        </div>

        <div class="tor-continuation-footer">
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
