@extends('layouts.registrar')

@section('title', 'PLP - Certificate of GWA')
@section('page-title', 'Certificate of GWA')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/certificate-gwa.css') }}?v={{ time() }}">
@endpush

@section('content')
@php
    $studentName = optional($student)->name ? strtoupper(optional($student)->name) : 'STUDENT NAME';
    $course = optional($student)->canonicalCourse;
    $programText = optional($course)->description
        ?: optional($course)->name
        ?: optional($student)->program
        ?: optional($course)->code
        ?: 'PROGRAM';
    $gwaText = isset($gwa) && $gwa !== null ? number_format($gwa, 2) : '-';
    $signaturePath = public_path('img/signature-registrar.png');
@endphp

<div class="pf-page">
    <div class="certificate-print-actions d-print-none">
        <button type="button" class="req-btn-save" onclick="window.print()">Print Certificate</button>
    </div>
    <article class="certificate-gwa">
        <header class="certificate-gwa__header">
            <h1 class="certificate-gwa__title">Certificate of<br>General Weighted Average</h1>
        </header>

        <section class="certificate-gwa__body" aria-label="Certificate text">
            <p class="certificate-gwa__paragraph certificate-gwa__paragraph--lead">
                This certifies that <span class="certificate-gwa__emphasis">{{ $studentName }}</span> who has completed
                all the academic requirements of the <span class="certificate-gwa__emphasis">{{ $programText }}</span>
                Program of the Pamantasan ng Lungsod ng Pasig has a General Weighted Average (GWA)
                of <span class="certificate-gwa__gwa">{{ $gwaText }}</span>.
            </p>

            <p class="certificate-gwa__paragraph certificate-gwa__paragraph--request">
                This certification is being issued upon the request of
                <span class="certificate-gwa__emphasis">{{ $studentName }}</span>
                for whatever legal purposes it may serve.
            </p>
        </section>

        <footer class="certificate-gwa__footer">
            <div class="certificate-gwa__signature">
                @if (file_exists($signaturePath))
                    <img
                        src="{{ asset('img/signature-registrar.png') }}"
                        alt="Registrar Signature"
                        class="certificate-gwa__signature-image"
                    >
               @endif

                <div class="certificate-gwa__signatory-name">FEDERICO G. NUEVA, MT</div>
                <div class="certificate-gwa__signatory-title">University Registrar</div>
            </div>

            <div class="certificate-gwa__seal">Not Valid Without<br>University Seal</div>
        </footer>
    </article>
</div>
@endsection
