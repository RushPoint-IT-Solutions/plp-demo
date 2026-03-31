@extends('layouts.registrar')

@section('title', 'PLP - Form No. 8C-2 Certificate of Graduation')
@section('page-title', 'Form No. 8C-2 Certificate of Graduation')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/certificate-graduation-8c2.css') }}?v={{ time() }}">
@endpush

@section('content')
@php
    $studentModel = isset($student) ? $student : null;

    $studentName = trim((string) optional($studentModel)->name);
    $studentName = $studentName !== '' ? strtoupper($studentName) : 'STUDENT NAME';

    $programText = trim((string) optional($studentModel)->program);
    $programText = $programText !== '' ? $programText : 'DEGREE/PROGRAM';

    $graduationDateValue = isset($graduation_date) ? $graduation_date : optional($studentModel)->graduation_date;
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
    <article class="certificate-8c2">
        <header class="certificate-8c2__header">
            <h1 class="certificate-8c2__title">Certificate of Graduation</h1>
        </header>

        <section class="certificate-8c2__body" aria-label="Certificate text">
            <p class="certificate-8c2__paragraph">
                This is to certify that
                <span class="certificate-8c2__emphasis">{{ $studentName }}</span>
                graduated from the university with the degree of
                <span class="certificate-8c2__emphasis">{{ $programText }}</span>
                on <span class="certificate-8c2__emphasis">{{ $graduationDateText }}</span>.
            </p>

            <p class="certificate-8c2__paragraph certificate-8c2__paragraph--request">
                This certification is issued upon the request of
                <span class="certificate-8c2__emphasis">{{ $studentName }}</span>
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

                    <div class="certificate-8c2__signatory-name">MR. FEDERICO G. NUEVA</div>
                    <div class="certificate-8c2__signatory-title">University Registrar</div>
            </div>

            <div class="certificate-8c2__seal">Not Valid Without<br>University Seal</div>
        </footer>
    </article>
</div>
@endsection
