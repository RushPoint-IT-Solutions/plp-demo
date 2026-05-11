<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Transcript of Records – {{ strtoupper($student->name) }}</title>
<style>
*  { box-sizing:border-box; margin:0; padding:0; font-family:'Times New Roman',serif; }
body { background:#f0f0f0; }

/* ── toolbar (hidden on print) ── */
.tor-toolbar {
    background:#004d27; color:#fff; padding:10px 24px;
    display:flex; align-items:center; justify-content:space-between;
    gap:12px; position:sticky; top:0; z-index:100;
}
.tor-toolbar span { font-size:14px; font-weight:600; }
.tor-toolbar a  { color:rgba(255,255,255,.75); font-size:13px; text-decoration:none; }
.tor-toolbar a:hover { color:#fff; }
.tor-print-btn {
    background:#fff; color:#004d27; border:none; border-radius:6px;
    padding:7px 20px; font-size:13px; font-weight:700; cursor:pointer; font-family:sans-serif;
}
.tor-print-btn:hover { background:#e2f5ea; }

/* ── page ── */
.tor-page {
    width:215.9mm; min-height:279.4mm; margin:20px auto;
    background:#fff; padding:15mm 20mm; position:relative;
    box-shadow:0 4px 24px rgba(0,0,0,.18);
}

/* ── header ── */
.tor-header { text-align:center; margin-bottom:8mm; }
.tor-logo-row { display:flex; align-items:center; justify-content:center; gap:14px; margin-bottom:4px; }
.tor-logo { width:60px; height:60px; object-fit:contain; }
.tor-school-name { font-size:15pt; font-weight:700; text-transform:uppercase; letter-spacing:.04em; }
.tor-school-addr { font-size:9pt; color:#444; }
.tor-doc-title {
    font-size:13pt; font-weight:700; letter-spacing:.15em; text-transform:uppercase;
    margin-top:5mm; border-top:2px solid #000; border-bottom:2px solid #000;
    padding:3px 0;
}

/* ── student info box ── */
.tor-info-grid {
    display:grid; grid-template-columns:1fr 1fr; gap:4px 20px;
    margin-bottom:6mm; font-size:9.5pt;
}
.tor-info-row { display:flex; gap:6px; }
.tor-info-lbl { font-weight:700; white-space:nowrap; min-width:100px; }
.tor-info-val { border-bottom:1px solid #555; flex:1; padding-bottom:1px; }

/* ── grade table ── */
.tor-term-title {
    background:#004d27; color:#fff; font-size:9pt; font-weight:700;
    padding:4px 8px; margin-top:4mm; text-transform:uppercase; letter-spacing:.05em;
}
.tor-tbl { width:100%; border-collapse:collapse; font-size:9pt; margin-bottom:2mm; }
.tor-tbl th { background:#e8f5e9; font-weight:700; padding:4px 6px; border:1px solid #bbb; text-align:left; }
.tor-tbl td { padding:3px 6px; border:1px solid #ddd; vertical-align:top; }
.tor-tbl td.grade-cell { text-align:center; font-weight:600; }
.tor-tbl tr:nth-child(even) td { background:#fafffe; }
.tor-tbl tfoot td { font-weight:700; background:#f1f5f9; border-top:2px solid #888; }

/* ── GWA / summary ── */
.tor-gwa-box {
    border:1.5px solid #004d27; border-radius:6px; padding:6px 14px;
    display:inline-flex; gap:20px; align-items:center; margin:4mm 0;
    font-size:9.5pt;
}
.tor-gwa-num { font-size:14pt; font-weight:700; color:#004d27; }

/* ── signatories ── */
.tor-sig-row { display:flex; justify-content:flex-end; margin-top:12mm; }
.tor-sig-block { text-align:center; min-width:160px; }
.tor-sig-line { border-top:1.5px solid #000; margin-bottom:3px; }
.tor-sig-name { font-size:10pt; font-weight:700; text-transform:uppercase; }
.tor-sig-title { font-size:8.5pt; color:#444; }

/* ── footer note ── */
.tor-footer-note {
    margin-top:8mm; font-size:8pt; color:#555; font-style:italic;
    border-top:1px solid #ccc; padding-top:4px;
    display:flex; justify-content:space-between;
}

/* ── print ── */
@media print {
    body { background:#fff; }
    .tor-toolbar { display:none !important; }
    .tor-page { margin:0; box-shadow:none; padding:10mm 15mm; }
    @page { size:letter portrait; margin:10mm; }
}
</style>
</head>
<body>

<div class="tor-toolbar">
    <span>Transcript of Records — {{ strtoupper($student->name) }}</span>
    <div style="display:flex;gap:12px;align-items:center;">
        <a href="{{ url()->previous() }}">← Back to Profile</a>
        <button class="tor-print-btn" onclick="window.print()">🖨 Print</button>
    </div>
</div>

<div class="tor-page">

    {{-- Header --}}
    <div class="tor-header">
        <div class="tor-logo-row">
            <img src="{{ asset('images/plp-logo.png') }}" class="tor-logo" alt="PLP Logo" onerror="this.style.display='none'">
            <div>
                <div class="tor-school-name">Pamantasan ng Lungsod ng Pasig</div>
                <div class="tor-school-addr">Alcalde Jose St., Kapasigan, Pasig City</div>
            </div>
        </div>
        <div class="tor-doc-title">Official Transcript of Records</div>
    </div>

    {{-- Student Info --}}
    @php
        $prof      = $student->profile;
        $fullName  = $prof ? strtoupper(trim($prof->last_name . ', ' . $prof->first_name . ' ' . ($prof->middle_name ? $prof->middle_name[0].'.' : '') . ($prof->suffix ? ' ' . $prof->suffix : ''))) : strtoupper($student->name);
        $course    = optional($student->canonicalCourse)->name ?: $student->program ?: '—';
        $courseCode= optional($student->canonicalCourse)->code ?: '—';
    @endphp
    <div class="tor-info-grid" style="margin-bottom:5mm;">
        <div class="tor-info-row"><span class="tor-info-lbl">Student Name:</span><span class="tor-info-val">{{ $fullName }}</span></div>
        <div class="tor-info-row"><span class="tor-info-lbl">Student No.:</span><span class="tor-info-val">{{ $student->student_no ?? '—' }}</span></div>
        <div class="tor-info-row"><span class="tor-info-lbl">Program:</span><span class="tor-info-val">{{ $course }}</span></div>
        <div class="tor-info-row"><span class="tor-info-lbl">Date Printed:</span><span class="tor-info-val">{{ now()->format('F j, Y') }}</span></div>
        @if($prof && $prof->date_of_birth)
        <div class="tor-info-row"><span class="tor-info-lbl">Date of Birth:</span><span class="tor-info-val">{{ $prof->date_of_birth->format('F j, Y') }}</span></div>
        @endif
        <div class="tor-info-row"><span class="tor-info-lbl">Printed By:</span><span class="tor-info-val">{{ optional(auth()->user())->name ?? 'Registrar' }}</span></div>
    </div>

    {{-- Grade Tables --}}
    @if($gradesBySyTerm->isEmpty())
        <p style="text-align:center;color:#888;font-size:10pt;margin:8mm 0;">No grade records on file.</p>
    @else
        @php $termTotalUnits = 0; $termTotalGP = 0; @endphp
        @foreach($gradesBySyTerm as $syTerm => $rows)
            @php
                [$sy, $term] = explode('|||', $syTerm);
                $semUnits = $rows->sum(function($r) { return is_numeric($r->units) ? (float)$r->units : 0; });
            @endphp
            <div class="tor-term-title">{{ $sy }} &nbsp;·&nbsp; {{ $term }} Semester</div>
            <table class="tor-tbl">
                <thead>
                    <tr>
                        <th style="width:90px;">Subject Code</th>
                        <th>Descriptive Title</th>
                        <th style="width:45px;text-align:center;">Units</th>
                        <th style="width:55px;text-align:center;">Final Grade</th>
                        <th style="width:65px;text-align:center;">Status</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $r)
                    @php
                        $grade = $r->final_grade;
                        $inc   = $r->inc;
                        $isPassed = !$inc && is_numeric($grade) && (float)$grade <= 3.0;
                        $isFailed = !$inc && is_numeric($grade) && (float)$grade > 3.0;
                    @endphp
                    <tr>
                        <td>{{ $r->subject_code }}</td>
                        <td>{{ $r->description ?: $r->equiv_subject_code ?: '—' }}</td>
                        <td class="grade-cell">{{ is_numeric($r->units) ? number_format((float)$r->units,1) : '—' }}</td>
                        <td class="grade-cell">{{ $inc ? 'INC' : ($grade !== null ? $grade : '—') }}</td>
                        <td class="grade-cell" style="font-size:8pt;color:{{ $isPassed ? '#15803d' : ($isFailed ? '#b91c1c' : '#555') }}">
                            {{ $inc ? 'Incomplete' : ($isPassed ? 'PASSED' : ($isFailed ? 'FAILED' : ($grade !== null ? 'PENDING' : '—'))) }}
                        </td>
                        <td style="font-size:8pt;color:#555;">{{ $r->remarks ?? '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" style="text-align:right;">Semester Total</td>
                        <td class="grade-cell">{{ number_format($semUnits,1) }}</td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
        @endforeach
    @endif

    {{-- GWA Summary --}}
    <div style="margin-top:5mm;display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
        <div class="tor-gwa-box">
            <div>
                <div style="font-size:8pt;color:#555;margin-bottom:2px;">GENERAL WEIGHTED AVERAGE</div>
                <div class="tor-gwa-num">{{ $gwa !== null ? number_format($gwa,4) : 'N/A' }}</div>
            </div>
            <div style="border-left:1px solid #ccc;padding-left:16px;">
                <div style="font-size:8pt;color:#555;margin-bottom:2px;">UNITS EARNED</div>
                <div style="font-size:13pt;font-weight:700;color:#004d27;">{{ number_format($totalUnitsEarned,1) }}</div>
            </div>
        </div>
    </div>

    {{-- Signatory --}}
    <div class="tor-sig-row">
        <div class="tor-sig-block">
            @if($registrar && $registrar->signature_path && file_exists(public_path($registrar->signature_path)))
                <img src="{{ asset($registrar->signature_path) }}" style="height:40px;margin-bottom:4px;" alt="signature">
            @else
                <div style="height:44px;"></div>
            @endif
            <div class="tor-sig-line"></div>
            <div class="tor-sig-name">{{ $registrar->signer_name ?? 'University Registrar' }}</div>
            <div class="tor-sig-title">{{ $registrar->designation_name ?? 'University Registrar' }}</div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="tor-footer-note">
        <span>This document is not valid without the official dry seal of the University Registrar.</span>
        <span>Printed: {{ now()->format('m/d/Y g:i A') }}</span>
    </div>

</div>

<script>
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'p') { e.preventDefault(); window.print(); }
});
</script>
</body>
</html>
