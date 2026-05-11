<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Diploma – {{ strtoupper($student->name) }}</title>
<style>
* { box-sizing:border-box; margin:0; padding:0; font-family:'Times New Roman',serif; }
body { background:#f0f0f0; }

/* toolbar */
.dip-toolbar {
    background:#004d27; color:#fff; padding:10px 24px;
    display:flex; align-items:center; justify-content:space-between; gap:12px;
    position:sticky; top:0; z-index:100;
}
.dip-toolbar span { font-size:14px; font-weight:600; }
.dip-toolbar a { color:rgba(255,255,255,.75); font-size:13px; text-decoration:none; }
.dip-toolbar a:hover { color:#fff; }
.dip-print-btn {
    background:#fff; color:#004d27; border:none; border-radius:6px;
    padding:7px 20px; font-size:13px; font-weight:700; cursor:pointer; font-family:sans-serif;
}
.dip-print-btn:hover { background:#e2f5ea; }

/* page — landscape letter */
.dip-page {
    width:279.4mm; min-height:215.9mm;
    margin:20px auto; background:#fff;
    padding:18mm 22mm; position:relative;
    box-shadow:0 4px 24px rgba(0,0,0,.18);
    display:flex; flex-direction:column; align-items:center; justify-content:space-between;
}

/* decorative border */
.dip-page::before {
    content:'';
    position:absolute; inset:8mm;
    border:3px double #8b6914;
    pointer-events:none;
}

/* seal watermark (optional visual) */
.dip-watermark {
    position:absolute; inset:0;
    display:flex; align-items:center; justify-content:center;
    pointer-events:none; opacity:.04;
}
.dip-watermark img { width:300px; height:300px; object-fit:contain; }

/* header */
.dip-header { text-align:center; }
.dip-logo-row { display:flex; align-items:center; justify-content:center; gap:16px; margin-bottom:4mm; }
.dip-logo { width:70px; height:70px; object-fit:contain; }
.dip-school-name { font-size:16pt; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#2d2d2d; }
.dip-school-addr { font-size:9pt; color:#555; margin-top:2px; }
.dip-republic { font-size:9pt; color:#555; margin-top:2px; letter-spacing:.08em; text-transform:uppercase; }

/* title */
.dip-title {
    font-size:28pt; font-weight:700; letter-spacing:.12em;
    text-transform:uppercase; color:#8b6914;
    margin:6mm 0 4mm;
    font-variant:small-caps;
}

/* body text */
.dip-certifies { font-size:11pt; color:#333; margin-bottom:5mm; }
.dip-student-name {
    font-size:26pt; font-style:italic; color:#1a1a1a;
    font-weight:700; margin:2mm 0; letter-spacing:.03em;
    border-bottom:1.5px solid #8b6914; display:inline-block; padding:0 8mm;
}
.dip-course-line { font-size:11.5pt; color:#333; margin-top:4mm; line-height:1.8; }
.dip-course-name { font-size:16pt; font-weight:700; color:#004d27; text-transform:uppercase; letter-spacing:.06em; }
.dip-date-line { font-size:11pt; color:#333; margin-top:4mm; }
.dip-so { font-size:9pt; color:#666; margin-top:2mm; font-style:italic; }

/* signatories */
.dip-sig-row {
    display:flex; justify-content:space-between;
    width:100%; margin-top:8mm; gap:16mm;
}
.dip-sig-block { text-align:center; flex:1; }
.dip-sig-img { height:44px; margin-bottom:3px; }
.dip-sig-line { border-top:1.5px solid #000; margin-bottom:3px; }
.dip-sig-name { font-size:10pt; font-weight:700; text-transform:uppercase; }
.dip-sig-title { font-size:8.5pt; color:#444; line-height:1.3; }

/* print */
@media print {
    body { background:#fff; }
    .dip-toolbar { display:none !important; }
    .dip-page { margin:0; box-shadow:none; padding:14mm 18mm; }
    @page { size:letter landscape; margin:8mm; }
}
</style>
</head>
<body>

<div class="dip-toolbar">
    <span>Diploma — {{ strtoupper($student->name) }}</span>
    <div style="display:flex;gap:12px;align-items:center;">
        <a href="{{ url()->previous() }}">← Back to Profile</a>
        <button class="dip-print-btn" onclick="window.print()">🖨 Print</button>
    </div>
</div>

<div class="dip-page">

    {{-- Watermark --}}
    <div class="dip-watermark">
        <img src="{{ asset('images/plp-logo.png') }}" alt="" onerror="this.style.display='none'">
    </div>

    {{-- Header --}}
    <div class="dip-header">
        <div class="dip-logo-row">
            <img src="{{ asset('images/plp-logo.png') }}" class="dip-logo" alt="PLP Logo" onerror="this.style.display='none'">
            <div>
                <div class="dip-republic">Republic of the Philippines</div>
                <div class="dip-school-name">Pamantasan ng Lungsod ng Pasig</div>
                <div class="dip-school-addr">Alcalde Jose St., Kapasigan, Pasig City</div>
            </div>
            <img src="{{ asset('images/plp-logo.png') }}" class="dip-logo" alt="" style="opacity:0;" onerror="this.style.display='none'">
        </div>

        <div class="dip-title">Diploma</div>

        <div class="dip-certifies">This is to certify that</div>

        @php
            $prof      = $student->profile;
            $fullName  = $prof
                ? trim($prof->first_name . ' ' . ($prof->middle_name ? $prof->middle_name[0].'.' : '') . ' ' . $prof->last_name . ($prof->suffix ? ', '.$prof->suffix : ''))
                : $student->name;
            $courseName = optional($student->canonicalCourse)->name ?: $student->program ?: 'the program';
            $gt = $graduateTagging;
        @endphp

        <div class="dip-student-name">{{ $fullName }}</div>

        <div class="dip-course-line">
            having satisfactorily completed all the requirements for the degree of<br>
            <span class="dip-course-name">{{ $courseName }}</span>
        </div>

        <div class="dip-date-line">
            is hereby conferred all the rights, privileges, and responsibilities thereunto appertaining<br>
            on
            @if($gt && $gt->date_graduated)
                the <strong>{{ $gt->date_graduated->format('jS') }} day of {{ $gt->date_graduated->format('F, Y') }}</strong>
            @else
                <span style="border-bottom:1px solid #000;display:inline-block;min-width:100px;">&nbsp;</span>
            @endif
        </div>

        @if($gt && $gt->so_number)
        <div class="dip-so">S.O. No. {{ $gt->so_number }}{{ $gt->so_date ? ', dated '.$gt->so_date->format('F j, Y') : '' }}</div>
        @endif
    </div>

    {{-- Signatories --}}
    <div class="dip-sig-row">
        @if($registrar)
        <div class="dip-sig-block">
            @if($registrar->signature_path && file_exists(public_path($registrar->signature_path)))
                <img src="{{ asset($registrar->signature_path) }}" class="dip-sig-img" alt="signature">
            @else
                <div style="height:47px;"></div>
            @endif
            <div class="dip-sig-line"></div>
            <div class="dip-sig-name">{{ $registrar->signer_name }}</div>
            <div class="dip-sig-title">{{ $registrar->designation_name }}</div>
        </div>
        @endif

        @if($president)
        <div class="dip-sig-block">
            @if($president->signature_path && file_exists(public_path($president->signature_path)))
                <img src="{{ asset($president->signature_path) }}" class="dip-sig-img" alt="signature">
            @else
                <div style="height:47px;"></div>
            @endif
            <div class="dip-sig-line"></div>
            <div class="dip-sig-name">{{ $president->signer_name }}</div>
            <div class="dip-sig-title">{{ $president->designation_name }}</div>
        </div>
        @endif
    </div>

</div>

<script>
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'p') { e.preventDefault(); window.print(); }
});
</script>
</body>
</html>
