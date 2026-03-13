@extends('layouts.applicant')

@section('title', 'PLP - Schedule of Exam')
@section('page-title', 'SCHEDULE OF EXAM')

@section('content')
<div class="applicant-exam-wrap">

    {{-- Applicant ID + Name --}}
    <div class="applicant-info-row">
        <div class="applicant-info-field">
            <label class="applicant-field-label">APPLICANT ID</label>
            <input type="text" class="applicant-input applicant-readonly" value="{{ $applicant->applicant_id }}" readonly>
        </div>
        <div class="applicant-info-field flex-grow-1">
            <label class="applicant-field-label">APPLICANT NAME</label>
            <input type="text" class="applicant-input applicant-readonly" value="{{ $applicant->first_name }} {{ $applicant->last_name }}" readonly>
        </div>
    </div>

    {{-- Exam Permit / Schedule Area --}}
    <div class="applicant-permit-box">
        @if($applicant->exam_date)
        <div class="applicant-permit-details">
            <div class="permit-detail-row">
                <span class="permit-label">Exam Date:</span>
                <span class="permit-value">{{ $applicant->exam_date->format('F d, Y \a\t g:i A') }}</span>
            </div>
            <div class="permit-detail-row">
                <span class="permit-label">Room:</span>
                <span class="permit-value">{{ $applicant->exam_room ?? '—' }}</span>
            </div>
        </div>
        @endif

        {{-- Reminders --}}
        <div class="applicant-reminders">
            <p class="reminders-heading"><strong>REMINDERS:</strong></p>
            <ul>
                <li>Bring your PLP-Examination permit to be allowed to take the test.</li>
                <li>Arrive at least 30 minutes before the exam begins.</li>
                <li>Do not bring calculators, cellphones, or any other computing devices; their use is strictly prohibited.</li>
                <li>Avoid eating during the test; it is strictly prohibited.</li>
                <li>Chaperones are not permitted to remain in the corridor during the examination.</li>
                <li>Bring one black ballpoint pen.</li>
                <li>Prepare two lead pencils with erasers.</li>
                <li>Remember to bring your own water.</li>
            </ul>

            <p class="reminders-heading mt-3"><strong>DRESS CODE</strong></p>
            <ul>
                <li>Wear a white top, polo shirt, or blouse.</li>
                <li>Avoid wearing tattered, torn, or ripped jeans or pants.</li>
                <li>Shorts and mini skirts are not permitted.</li>
                <li>Opt for closed, comfortable shoes</li>
            </ul>
        </div>
    </div>

</div>
@endsection
