@extends('layouts.registrar')

@section('title', 'PLP - Certificate of GWA')
@section('page-title', 'Certificate of GWA')
@push('styles')
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
                <p class="cert-body__p--center cert-body__p--mb-lg">
                    This certifies that <span class="student-name">{{ $studentName }}</span> who has completed
                    all the academic requirements of the Bachelor of Science in Information Technology
                    Program of the Pamantasan ng Lungsod ng Pasig has a General Weighted Average (GWA)
                    of <span class="cert-gwa">{{ $gwaText }}</span>.
                </p>

                <p class="cert-body__p--center">
                    This certification is being issued upon the request of <span class="student-name">{{ $studentName }}</span>
                    for whatever legal purposes it may serve.
                </p>

                <div class="cert-sign">
                    @php $sigFile = public_path('img/signature-registrar.png'); @endphp
                    @if (file_exists($sigFile))
                        <img src="{{ asset('img/signature-registrar.png') }}" alt="Registrar Signature" class="cert-sign__img">
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
