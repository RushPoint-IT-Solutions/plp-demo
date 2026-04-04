@extends('layouts.applicant')

@section('title', 'PLP - Official Correspondence')
@section('page-title', 'OFFICIAL CORRESPONDENCE')

@section('content')
@php
    $preference = optional($applicant->applicationPreference);
    $preferredCourse = optional($preference->course)->name;
    $courseLabel = $preferredCourse ?: ($preference->apply_strand ?: 'Chosen Course');
    $resultStatus = strtolower((string) ($applicant->exam_result_status ?: 'pending'));

    $decisionWord = 'UNDER REVIEW';
    if ($resultStatus === 'passed') {
        $decisionWord = 'ACCEPTED';
    } elseif ($resultStatus === 'failed') {
        $decisionWord = 'NOT ACCEPTED';
    }

    $referenceCode = 'PLP-APP-' . strtoupper((string) ($applicant->applicant_id ?: '0000'));
@endphp
<div class="app-process-page applicant-consistent-page">
    <div class="applicant-correspondence-shell">
        <div class="applicant-correspondence-head">
            <img src="{{ asset('img/logobg.png') }}" alt="PLP Logo" class="applicant-correspondence-logo">
            <div class="applicant-correspondence-meta">
                <h3>PLP ADMISSIONS OFFICE</h3>
                <p>Application Status Notification: {{ $applicant->first_name }} {{ $applicant->last_name }}</p>
            </div>
            <div class="applicant-correspondence-date">{{ now()->format('F d, Y, h:i A') }}</div>
        </div>

        <div class="applicant-correspondence-divider"></div>

        <div class="applicant-correspondence-body">
            <p>
                Congratulations! We are pleased to inform you that your application to Pamantasan ng Lungsod ng Pasig
                has been <strong>{{ $decisionWord }}</strong> to the <strong>{{ $courseLabel }}</strong> program.
            </p>

            <p>Welcome to our community!</p>

            <p>[Admissions Officer Name]</p>

            <div class="applicant-ref-chip">
                <span class="dot"></span>
                <span>REFERENCE NUMBER: {{ $referenceCode }}</span>
            </div>

            <div class="applicant-next-steps">
                <p>Next Steps:</p>
                <ol>
                    <li>Wait for an official appointment email.</li>
                    <li>Prepare your original physical documents.</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection
