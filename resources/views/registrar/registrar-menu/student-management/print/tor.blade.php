<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
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
.tor-print-btn {
    background:#fff; color:#004d27; border:none; border-radius:6px;
    padding:7px 20px; font-size:13px; font-weight:700; cursor:pointer; font-family:Arial,sans-serif;
}
.tor-print-btn:hover { background:#e2f5ea; }

/* ── page ── */
.tor-page {
    width:215.9mm; min-height:279.4mm; margin:20px auto;
    background:#fff; padding:12mm 15mm; position:relative;
    box-shadow:0 4px 24px rgba(0,0,0,.18);
    font-size:10pt; line-height:1.35;
}

/* ── header space — blank; the letterhead is pre-printed on the physical paper ── */
.tor-header-space { height:45mm; }

/* ── section bar (STUDENT DATA / SCHOLASTIC RECORD) ── */
.tor-section-bar {
    text-align:center; font-weight:700; font-size:11pt;
    border-top:2px solid #000; border-bottom:2px solid #000;
    padding:1.5mm 0; margin:3mm 0 2mm;
}

/* ── student data ── */
.tor-sd-wrap { display:flex; gap:6mm; margin-bottom:2mm; }
.tor-sd-fields { flex:1; }
.tor-sd-row { display:flex; gap:6px; padding:0.6mm 0; font-size:10pt; }
.tor-sd-label { font-weight:700; min-width:150px; flex-shrink:0; }
.tor-sd-sep { font-weight:700; margin-right:6px; }
.tor-sd-photo { width:140px; text-align:center; flex-shrink:0; }
.tor-sd-photo-box {
    width:130px; height:150px; border:1px solid #000; display:flex;
    align-items:center; justify-content:center; overflow:hidden; background:#fafafa;
}
.tor-sd-photo-box img { width:100%; height:100%; object-fit:cover; }
.tor-sd-photo-caption { font-size:8pt; font-style:italic; margin-top:2mm; }

/* ── scholastic record ── */
.tor-sr-row { display:flex; gap:6px; padding:0.6mm 0; font-size:10pt; }
.tor-sr-label { font-weight:700; min-width:150px; flex-shrink:0; }

/* ── grading system + remarks ── */
.tor-gs-wrap { display:flex; gap:0; margin:2mm 0; border:2px solid #000; }
.tor-gs-box { flex:1.4; padding:2mm 3mm; border-right:2px solid #000; }
.tor-gs-title { text-align:center; font-weight:700; font-size:10pt; margin-bottom:1.5mm; }
.tor-gs-grid { display:grid; grid-template-columns:1fr 1fr auto; gap:0 8mm; font-size:9pt; }
.tor-gs-grid div { padding:0.3mm 0; }
.tor-gs-codes { font-size:9pt; }
.tor-gs-credits { font-size:8pt; margin-top:2mm; line-height:1.3; }
.tor-gs-credits strong { display:block; }
.tor-remarks-box { flex:1; padding:2mm 3mm; display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; }
.tor-remarks-title { font-weight:700; font-size:10pt; margin-bottom:2mm; }
.tor-remarks-val { font-weight:700; font-size:10pt; }

/* ── legal notice ── */
.tor-legal { font-size:8.5pt; margin:2mm 0; text-align:justify; }

/* ── grade table (continuation pages reuse this) ── */
.tor-name-line { font-size:10pt; margin:2mm 0 1mm; display:flex; align-items:baseline; gap:6px; }
.tor-name-line b { display:inline-block; min-width:90px; flex-shrink:0; }
.tor-name-line span { flex:1; border-bottom:1px solid #000; padding-bottom:1px; }
.tor-tbl { width:100%; border-collapse:collapse; font-size:9.5pt; margin-bottom:2mm; border:2px solid #000; }
.tor-tbl thead { display:table-header-group; }
.tor-tbl th { border:1px solid #000; padding:1.5mm 2mm; text-align:center; font-weight:700; font-size:9pt; }
.tor-tbl td { border:1px solid #000; padding:1mm 2mm; vertical-align:top; }
.tor-tbl td.grade-cell { text-align:center; }
.tor-sem-row td { font-weight:700; font-style:normal; }

/* ── legal notice + signatures (page 1 only) ── */
.tor-sig-grid { display:grid; grid-template-columns:1fr 1fr; gap:0 10mm; margin-top:10mm; }
.tor-sig-cell { text-align:center; }
.tor-sig-cell-label { text-align:left; font-weight:700; font-size:10pt; margin-bottom:10mm; }
.tor-sig-img { height:34px; margin-bottom:1mm; }
.tor-sig-line { border-top:1px solid #000; margin:0 8mm; padding-top:1mm; }
.tor-sig-name { font-weight:700; font-size:10pt; }
.tor-sig-title { font-size:9pt; font-style:italic; }
.tor-cert-block { text-align:center; margin-top:18mm; }
.tor-cert-label { font-weight:700; font-size:10pt; margin-bottom:10mm; }

/* ── footer ── */
.tor-footer { margin-top:8mm; display:flex; align-items:flex-end; justify-content:space-between; gap:6mm; }
.tor-footer-seal { display:flex; align-items:center; gap:3mm; }
.tor-footer-seal-circle {
    width:50px; height:50px; border-radius:50%; border:2px solid #b45309; color:#b45309;
    display:flex; align-items:center; justify-content:center; text-align:center; font-size:6pt; font-weight:700; flex-shrink:0;
}
.tor-footer-note { font-size:8.5pt; font-style:italic; }
.tor-footer-meta { font-size:8.5pt; }
.tor-continuation-footer { margin-top:4mm; font-size:8pt; display:flex; justify-content:space-between; align-items:flex-end; }

/* ── print ── */
@media print {
    body { background:#fff; }
    .tor-toolbar { display:none !important; }
    .tor-page { margin:0; box-shadow:none; padding:10mm 14mm; width:auto; min-height:0; }
    .tor-tbl tr { page-break-inside:avoid; }
    @page { size:letter portrait; margin:10mm; }
}
</style>
</head>
<body>

<div class="tor-toolbar">
    <span>Transcript of Records — {{ strtoupper($student->name) }}</span>
    <div style="display:flex;gap:12px;align-items:center;">
        <label style="font-size:13px;font-family:Arial,sans-serif;display:flex;gap:6px;align-items:center;">
            Purpose:
            <select onchange="window.location='{{ url()->current() }}?purpose='+encodeURIComponent(this.value)" style="font-family:Arial,sans-serif;font-size:12px;padding:3px 6px;border-radius:4px;border:none;">
                @foreach($purposeOptions as $option)
                    <option value="{{ $option }}" {{ $purpose === $option ? 'selected' : '' }}>{{ $option }}</option>
                @endforeach
            </select>
        </label>
        <a href="{{ url()->previous() }}">← Back to Profile</a>
        <button class="tor-print-btn" onclick="window.print()">🖨 Print</button>
    </div>
</div>

<div class="tor-page">

    @php
        $prof      = $student->profile;
        $fullName  = $prof ? strtoupper(trim($prof->last_name . ', ' . $prof->first_name . ' ' . ($prof->middle_name ? $prof->middle_name[0].'.' : '') . ($prof->suffix ? ' ' . $prof->suffix : ''))) : strtoupper($student->name);
        $course    = optional($student->canonicalCourse)->name ?: $student->program ?: '—';
        $courseCode= optional($student->canonicalCourse)->code ?: '—';
        $photoUrl  = ($prof && $prof->profile_photo_path && file_exists(public_path($prof->profile_photo_path))) ? asset($prof->profile_photo_path) : null;
    @endphp

    {{-- Letterhead space — left blank because the school header (seal, school name,
         office name, "Official Transcript of Records" title) is pre-printed on the
         physical paper stock this prints onto, same as the Report of Grades sheet. --}}
    <div class="tor-header-space"></div>

    {{-- STUDENT DATA --}}
    <div class="tor-section-bar">STUDENT DATA</div>
    <div class="tor-sd-wrap">
        <div class="tor-sd-fields">
            <div class="tor-sd-row"><span class="tor-sd-label">Student Number</span><span class="tor-sd-sep">:</span><span>{{ $student->student_no ?? 'N/A' }}</span></div>
            <div class="tor-sd-row"><span class="tor-sd-label">Name</span><span class="tor-sd-sep">:</span><span>{{ $fullName }}</span></div>
            <div class="tor-sd-row"><span class="tor-sd-label">Address</span><span class="tor-sd-sep">:</span><span>{{ $prof && $prof->present_municipality ? trim(($prof->present_street ? $prof->present_street.', ' : '') . ($prof->present_barangay ? $prof->present_barangay.', ' : '') . $prof->present_municipality . ', ' . $prof->present_province) : 'N/A' }}</span></div>
            <div class="tor-sd-row"><span class="tor-sd-label">Sex</span><span class="tor-sd-sep">:</span><span>{{ $student->sex ?? 'N/A' }}</span></div>
            <div class="tor-sd-row"><span class="tor-sd-label">Date of Birth</span><span class="tor-sd-sep">:</span><span>{{ ($prof && $prof->date_of_birth) ? $prof->date_of_birth->format('F j, Y') : 'N/A' }}</span></div>
            <div class="tor-sd-row"><span class="tor-sd-label">Place of Birth</span><span class="tor-sd-sep">:</span><span>{{ ($prof && $prof->place_of_birth) ? $prof->place_of_birth : 'N/A' }}</span></div>
            <div class="tor-sd-row"><span class="tor-sd-label">Date of Admission</span><span class="tor-sd-sep">:</span><span>N/A</span></div>
            <div class="tor-sd-row"><span class="tor-sd-label">Admission Credentials</span><span class="tor-sd-sep">:</span><span>N/A</span></div>
            <div class="tor-sd-row"><span class="tor-sd-label">Program</span><span class="tor-sd-sep">:</span><span>{{ $course }}{{ $courseCode !== '—' ? ' ('.$courseCode.')' : '' }}</span></div>
            @if($isGraduated)
            <div class="tor-sd-row"><span class="tor-sd-label">Date of Graduation</span><span class="tor-sd-sep">:</span><span>{{ $graduateTagging->date_graduated ? $graduateTagging->date_graduated->format('F j, Y') : 'N/A' }}</span></div>
            <div class="tor-sd-row"><span class="tor-sd-label">Resolution No.</span><span class="tor-sd-sep">:</span><span>{{ $graduateTagging->so_number ?: 'N/A' }}</span></div>
            @endif
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

    {{-- SCHOLASTIC RECORD --}}
    <div class="tor-section-bar">SCHOLASTIC RECORD</div>
    @php
        // K-12 curriculum students record Junior/Senior High School separately;
        // pre-K12 students only have a single "High School" entry.
        $isK12 = $prof && !$prof->no_k12 && ($prof->junior_school || $prof->senior_school);
    @endphp
    @if($isK12)
        <div class="tor-sr-row"><span class="tor-sr-label">Elementary</span><span class="tor-sd-sep">:</span><span>{{ $prof->elementary_school ?: 'N/A' }}</span></div>
        <div class="tor-sr-row"><span class="tor-sr-label">Junior High School</span><span class="tor-sd-sep">:</span><span>{{ $prof->junior_school ?: 'N/A' }}</span></div>
        <div class="tor-sr-row"><span class="tor-sr-label">Senior High School</span><span class="tor-sd-sep">:</span><span>{{ $prof->senior_school ?: 'N/A' }}</span></div>
    @else
        <div class="tor-sr-row"><span class="tor-sr-label">Elementary</span><span class="tor-sd-sep">:</span><span>{{ ($prof && $prof->elementary_school) ? $prof->elementary_school : 'N/A' }}</span></div>
        <div class="tor-sr-row"><span class="tor-sr-label">High School</span><span class="tor-sd-sep">:</span><span>{{ ($prof && $prof->high_school) ? $prof->high_school : 'N/A' }}</span></div>
    @endif
    <div class="tor-sr-row"><span class="tor-sr-label">School Last Attended</span><span class="tor-sd-sep">:</span><span>{{ ($prof && $prof->school_last_attended) ? $prof->school_last_attended : 'N/A' }}</span></div>

    {{-- GRADING SYSTEM + REMARKS --}}
    <div class="tor-gs-wrap">
        <div class="tor-gs-box">
            <div class="tor-gs-title">GRADING SYSTEM</div>
            <div class="tor-gs-grid">
                <div>1.00 = 97.5-100</div><div>2.25 = 82.5-85.4</div><div>INC &nbsp; Incomplete</div>
                <div>1.25 = 94.5-97.4</div><div>2.50 = 79.5-82.4</div><div>OD &nbsp;&nbsp; Officially Dropped</div>
                <div>1.50 = 91.5-94.4</div><div>2.75 = 76.5-79.4</div><div>UD &nbsp;&nbsp; Unofficially Dropped</div>
                <div>1.75 = 88.5-91.4</div><div>3.00 = 74.5-76.4</div><div>NC &nbsp;&nbsp; No Credit</div>
                <div>2.00 = 85.5-88.4</div><div>5.00 = 74.4 &amp; below</div><div>GNA &nbsp; Grade Not Available</div>
            </div>
            <div class="tor-gs-credits">
                <strong>Credits:</strong>
                One unit of credit is one hour lecture or recitation or three hours of laboratory work each week
                for the period of a complete semester. The medium of instruction in this University is English
                except courses in Filipino and other foreign languages.
            </div>
        </div>
        <div class="tor-remarks-box">
            <div class="tor-remarks-title">REMARKS</div>
            <div class="tor-remarks-val">{{ $purpose }}</div>
        </div>
    </div>

    {{-- Legal notice --}}
    <div class="tor-legal">
        This copy is an exact reproduction of the original transcript on file with the Office of the University Registrar
        and should be considered as an original copy when signed by the university registrar and impressed with the
        university seal. Any erasure or alteration on this transcript renders the whole document invalid unless
        authenticated by the signature of the foregoing official.
    </div>

    {{-- Signatures --}}
    <div class="tor-sig-grid">
        <div class="tor-sig-cell">
            <div class="tor-sig-cell-label" style="text-align:left;">Prepared by:</div>
            <div class="tor-sig-line">
                <div class="tor-sig-name">Registrar Staff</div>
                <div class="tor-sig-title">College Secretary</div>
            </div>
        </div>
        <div class="tor-sig-cell">
            <div class="tor-sig-cell-label" style="text-align:left;">Checked by:</div>
            <div class="tor-sig-line">
                @if($assistantRegistrar && $assistantRegistrar->signature_path && file_exists(public_path($assistantRegistrar->signature_path)))
                    <img src="{{ asset($assistantRegistrar->signature_path) }}" class="tor-sig-img" alt="signature">
                @endif
                <div class="tor-sig-name">{{ $assistantRegistrar->signer_name ?? 'Assistant Registrar' }}</div>
                <div class="tor-sig-title">{{ $assistantRegistrar->designation_name ?? 'Assistant Registrar' }}</div>
            </div>
        </div>
    </div>
    <div class="tor-cert-block">
        <div class="tor-cert-label">Certified True and Correct:</div>
        @if($registrar && $registrar->signature_path && file_exists(public_path($registrar->signature_path)))
            <img src="{{ asset($registrar->signature_path) }}" class="tor-sig-img" alt="signature">
        @endif
        <div class="tor-sig-line" style="display:inline-block;">
            <div class="tor-sig-name">{{ $registrar->signer_name ?? 'University Registrar' }}</div>
            <div class="tor-sig-title">{{ $registrar->designation_name ?? 'University Registrar' }}</div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="tor-footer">
        <div class="tor-footer-seal">
            <div class="tor-footer-seal-circle">PLP<br>SEAL</div>
            <div>
                <div class="tor-footer-note">Not Valid Without University Seal</div>
                <div class="tor-footer-meta">Date Issued: {{ now()->format('M d, Y') }}</div>
            </div>
        </div>
        <div class="tor-footer-meta">Page 1</div>
    </div>

    {{-- Grade Tables --}}
    <div style="page-break-before:always;"></div>
    <div class="tor-header-space"></div>
    <div class="tor-name-line"><b>NAME</b><span>{{ $fullName }}</span></div>
    <div class="tor-name-line"><b>STUDENT NO.</b><span>{{ $student->student_no ?? '—' }}</span></div>

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
                    <th style="width:70px;">Semestral</th>
                    <th style="width:80px;">Re-exam Completion</th>
                </tr>
            </thead>
            <tbody>
                @foreach($gradesBySyTerm as $syTerm => $rows)
                    @php
                        [$sy, $term] = explode('|||', $syTerm);
                    @endphp
                    <tr class="tor-sem-row">
                        <td colspan="5">{{ $sy }}, {{ strtoupper($term) }} SEMESTER</td>
                    </tr>
                    @foreach($rows as $r)
                    @php
                        $grade = $r->final_grade;
                        $inc   = $r->inc;
                    @endphp
                    <tr>
                        <td>{{ $r->subject_code }}</td>
                        <td>{{ $r->description ?: $r->equiv_subject_code ?: '—' }}</td>
                        <td class="grade-cell">{{ $inc ? 'INC' : (is_numeric($grade) ? number_format((float) $grade, 2) : ($grade ?: '—')) }}</td>
                        <td class="grade-cell"></td>
                        <td class="grade-cell">{{ is_numeric($r->units) ? number_format((float)$r->units,2) : '—' }}</td>
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
                <div style="font-size:13pt;font-weight:700;">{{ $gwa !== null ? number_format($gwa,4) : 'N/A' }}</div>
            </div>
            <div style="border-left:1px solid #999;padding-left:6mm;">
                <div style="font-size:8pt;margin-bottom:1mm;">UNITS EARNED</div>
                <div style="font-size:13pt;font-weight:700;">{{ number_format($totalUnitsEarned,1) }}</div>
            </div>
        </div>
    </div>

    {{-- Continuation footer --}}
    <div class="tor-continuation-footer">
        <div>
            {{ now()->format('n/j/Y g:i A') }}<br>
            Prepared by: {{ optional(auth()->user())->name ?? 'Registrar Staff' }}<br>
            Checked by: {{ $assistantRegistrar->signer_name ?? 'Assistant Registrar' }}
        </div>
        <div style="text-align:right;">
            <em>Not valid without university seal</em>
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
