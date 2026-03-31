@extends('layouts.registrar')

@section('title', 'PLP - Certificate of GWA')
@section('page-title', 'Certificate of GWA')

@push('styles')
<style>
@page { size: A4; margin: 25mm 20mm; }
@media print {
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
    .cert-container { page-break-inside: avoid; break-inside: avoid; }
    .cert-body { page-break-inside: avoid; break-inside: avoid; }
}
.cert-container {
    max-width: 180mm;
    margin: 0 auto;
    font-family: 'Times New Roman', Times, serif;
    color: #000;
    position: relative;
}
.cert-title {
    font-size: 24pt;
    font-weight: 700;
    text-align: center;
    margin-top: 18mm;
    line-height: 1.1;
    margin-bottom: 6mm;
}
.cert-body {
    font-size: 10pt;
    line-height: 1.45;
    margin: 0 10mm;
    text-align: justify;
    text-justify: inter-word;
}
.student-name { font-weight:700; text-transform:uppercase; text-decoration: underline; }
.cert-gwa { font-weight:700; }
.cert-sign { margin-top: 36mm; text-align:center; }
.sig-line { display:inline-block; border-top:1px solid #000; width:60mm; height: 1px; margin-top:12mm; }
.cert-footnote { font-size:9pt; position:absolute; left:20mm; bottom:18mm; color:#333; }
@media screen { .cert-container { padding-bottom: 40mm; } }
</style>
@endpush

@section('content')
<div class="pf-page">
    <div class="rep-group-card">
        <div class="cert-container">
            @php
                $studentName = optional($student)->name ? strtoupper(optional($student)->name) : 'STUDENT NAME';
                $program = optional($student)->program ? optional($student)->program : 'PROGRAM';
                $gwaText = isset($gwa) && $gwa !== null ? number_format($gwa, 2) : '-';
            @endphp

            <h1 class="cert-title">Certificate of<br>General Weighted Average</h1>

            <div class="cert-body">
                <p style="text-align:center; margin-bottom: 14mm;">
                    This certifies that <span class="student-name">{{ $studentName }}</span> who has completed
                    all the academic requirements of the Bachelor of Science in Information Technology
                    Program of the Pamantasan ng Lungsod ng Pasig has a General Weighted Average (GWA)
                    of <span class="cert-gwa">{{ $gwaText }}</span>.
                </p>

                <p style="text-align:center;">
                    This certification is being issued upon the request of <span class="student-name">{{ $studentName }}</span>
                    for whatever legal purposes it may serve.
                </p>

                <div class="cert-sign">
                    @php $sigFile = public_path('img/signature-registrar.png'); @endphp
                    @if (file_exists($sigFile))
                        <img src="{{ asset('img/signature-registrar.png') }}" alt="Registrar Signature" style="width:60mm; height:auto; display:block; margin: 0 auto;">
                    @else
                        <div class="sig-line" aria-hidden="true"></div>
                        <!-- signature placeholder: place scanned signature at public/img/signature-registrar.png -->
                    @endif
                    <!-- Registrar name/signature intentionally left blank per request -->
                </div>

                <div class="cert-footnote">Not Valid Without<br>University Seal</div>

            </div>
        </div>
    </div>
</div>
@endsection
