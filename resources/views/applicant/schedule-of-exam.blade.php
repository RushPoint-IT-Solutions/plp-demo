@extends('layouts.applicant')

@section('title', 'PLP - Schedule of Exam')
@section('page-title', 'SCHEDULE OF EXAM')

@section('content')
<div class="app-process-page applicant-consistent-page">
    <div class="app-filter-bar">
        <div class="app-filter-row">
            <div class="app-filter-group" style="max-width: 260px;">
                <label class="app-filter-label">Applicant ID</label>
                <input type="text" class="app-filter-input" value="{{ $applicant->applicant_id }}" readonly>
            </div>
            <div class="app-filter-group" style="max-width: 260px;">
                <label class="app-filter-label">Applicant Name</label>
                <input type="text" class="app-filter-input" value="{{ $applicant->first_name }} {{ $applicant->last_name }}" readonly>
            </div>
        </div>
    </div>

    <div class="student-table-wrapper applicant-content-shell applicant-content-shell-schedule">
        <div class="applicant-permit-box" style="margin-bottom: 14px;">
            <div class="applicant-reminders" style="padding-bottom: 6px;">
                <p class="reminders-heading"><strong>EXAM SCHEDULE DETAILS</strong></p>
                @if($applicant->exam_date)
                    <ul>
                        <li><strong>Date:</strong> {{ optional($applicant->exam_date)->format('F d, Y') }}</li>
                        <li><strong>Time:</strong> {{ optional($applicant->exam_date)->format('h:i A') }}</li>
                        <li><strong>Room:</strong> {{ $applicant->exam_room ?: 'TBA' }}</li>
                        <li><strong>Status:</strong> {{ $applicant->exam_result_status ?: 'Pending' }}</li>
                    </ul>
                @else
                    <p style="margin:0; color:#555;">Your exam schedule is not yet available. Please check back later.</p>
                @endif
            </div>
        </div>

        <div class="applicant-permit-box applicant-permit-box-schedule">
            <div class="applicant-reminders applicant-reminders-schedule">
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
</div>
@endsection
