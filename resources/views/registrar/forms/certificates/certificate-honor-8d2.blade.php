@extends('layouts.registrar')

@section('title', 'PLP - Form No. 8D-2 Certificate of Honor')
@section('page-title', 'Form No. 8D-2 Certificate of Honor')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/certificate-honor-8d2.css') }}?v={{ time() }}">
@endpush

@section('content')
@php
    $studentModel = isset($student) ? $student : null;

    $studentName = trim((string) optional($studentModel)->name);
    $studentName = $studentName !== '' ? strtoupper($studentName) : 'STUDENT NAME';

    $programText = trim((string) optional($studentModel)->program);
    if ($programText === '' && $studentModel && $studentModel->relationLoaded('canonicalCourse')) {
        $programText = trim((string) (optional($studentModel->canonicalCourse)->name ?: optional($studentModel->canonicalCourse)->code));
    }
    $programText = $programText !== '' ? $programText : 'DEGREE/PROGRAM';

    $honorValue = isset($honor_title) ? $honor_title : (isset($honor) ? $honor : optional($studentModel)->honor);
    $honorText = trim((string) $honorValue);
    $honorText = $honorText !== '' ? strtoupper($honorText) : 'HONOR';

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
    <article class="certificate-8d2">
        <header class="certificate-8d2__header">
            <h1 class="certificate-8d2__title">Certificate of Honor</h1>
        </header>

        <section class="certificate-8d2__body" aria-label="Certificate text">
            <p class="certificate-8d2__paragraph">
                This is to certify that <span class="certificate-8d2__emphasis">{{ $studentName }}</span> graduated
                <span class="certificate-8d2__honor">{{ $honorText }}</span> from the university with the degree of
                <span class="certificate-8d2__emphasis">{{ $programText }}</span> on
                <span class="certificate-8d2__emphasis">{{ $graduationDateText }}</span>.
            </p>

            <p class="certificate-8d2__paragraph certificate-8d2__paragraph--request">
                This certification is being issued upon the request of
                <span class="certificate-8d2__emphasis">{{ $studentName }}</span>
                for whatever legal purposes it may serve.
            </p>
        </section>

        <footer class="certificate-8d2__footer">
            <div class="certificate-8d2__signature">
                @if (file_exists($signaturePath))
                    <img
                        src="{{ asset('img/signature-registrar.png') }}"
                        alt="Registrar Signature"
                        class="certificate-8d2__signature-image"
                    >
               @endif

                <div class="certificate-8d2__signatory-name">Mr. Federico G. Nueva</div>
                <div class="certificate-8d2__signatory-title">University Registrar</div>
            </div>

            <div class="certificate-8d2__seal">Not Valid Without<br>University Seal</div>
        </footer>
    </article>
</div>
@endsection
