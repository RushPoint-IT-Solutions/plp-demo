@extends('layouts.registrar')

@section('title', 'PLP - Form No. 8C-2 Certificate of Graduation')
@section('page-title', 'Form No. 8C-2 Certificate of Graduation')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/certificate-graduation-8c2.css') }}?v={{ time() }}">
@endpush

@section('content')
@php
    $studentModel = isset($student) ? $student : null;
    $prof = optional($studentModel)->profile;

    $honorific = strtolower((string) optional($studentModel)->sex) === 'female' ? 'MS.' : 'MR.';

    $firstMiddleLastName = trim(
        (string) optional($prof)->first_name
        . ' ' . (string) optional($prof)->middle_name
        . ' ' . (string) optional($prof)->last_name
        . ($prof && $prof->suffix ? ' ' . $prof->suffix : '')
    );
    $firstMiddleLastName = preg_replace('/\s+/', ' ', $firstMiddleLastName);
    $studentFullName = $firstMiddleLastName !== '' ? strtoupper($honorific . ' ' . $firstMiddleLastName) : 'STUDENT NAME';

    $lastNameOnly = trim((string) optional($prof)->last_name . ($prof && $prof->suffix ? ' ' . $prof->suffix : ''));
    $studentLastName = $lastNameOnly !== '' ? strtoupper($honorific . ' ' . $lastNameOnly) : $studentFullName;

    $programText = optional(optional($studentModel)->canonicalCourse)->description
        ?: optional(optional($studentModel)->canonicalCourse)->name
        ?: trim((string) optional($studentModel)->program);
    $programText = $programText !== '' ? $programText : 'DEGREE/PROGRAM';

    $graduationDateValue = isset($graduation_date) ? $graduation_date : optional(optional($studentModel)->graduateTagging)->date_graduated;
    $graduationDateText = 'DATE';

    if (!empty($graduationDateValue)) {
        try {
            $graduationDateText = \Carbon\Carbon::parse($graduationDateValue)->format('F j, Y');
        } catch (\Exception $exception) {
            $graduationDateText = (string) $graduationDateValue;
        }
    }

    $signaturePath = public_path('img/signature-registrar.png');
@endphp

<div class="pf-page">
    <div class="certificate-print-actions d-print-none">
        <button type="button" class="req-btn-save" onclick="window.print()">Print Certificate</button>
    </div>
    <article class="certificate-8c2">
        <header class="certificate-8c2__header">
            <h1 class="certificate-8c2__title">Certificate of Graduation</h1>
        </header>

        <section class="certificate-8c2__body" aria-label="Certificate text">
            <p class="certificate-8c2__paragraph">
                This is to certify that
                <span class="certificate-8c2__name">{{ $studentFullName }}</span>
                graduated from the university with the degree of
                <span class="certificate-8c2__degree">{{ $programText }}</span>
                on {{ $graduationDateText }}.
            </p>

            <p class="certificate-8c2__paragraph certificate-8c2__paragraph--request">
                This certification is issued upon the request of
                <span class="certificate-8c2__name">{{ $studentLastName }}</span>
                for whatever legal purposes it may serve.
            </p>
        </section>

        <footer class="certificate-8c2__footer">
            <div class="certificate-8c2__signature">
                @if (file_exists($signaturePath))
                    <img
                        src="{{ asset('img/signature-registrar.png') }}"
                        alt="Registrar Signature"
                        class="certificate-8c2__signature-image"
                    >
                @endif
                <div class="certificate-8c2__signatory-name">FEDERICO G. NUEVA, MT</div>
                <div class="certificate-8c2__signatory-title">University Registrar</div>
            </div>

            <div class="certificate-8c2__seal">Not Valid Without<br>University Seal</div>
        </footer>
    </article>
</div>
@endsection
