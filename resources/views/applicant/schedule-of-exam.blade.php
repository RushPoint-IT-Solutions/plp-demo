@extends('layouts.applicant')

@section('title', 'PLP - Schedule of Exam')
@section('page-title', 'SCHEDULE OF EXAM')

@section('content')
<div class="app-process-page applicant-consistent-page">
    <div class="app-filter-bar">
        <div class="app-filter-row">
            <div class="app-filter-group" style="max-width: 260px;">
                <label class="app-filter-label">Applicant ID</label>
                <input type="text" class="app-filter-input" value="{{ $applicant->applicant_id }}">
            </div>
            <div class="app-filter-group" style="max-width: 260px;">
                <label class="app-filter-label">Applicant Name</label>
                <input type="text" class="app-filter-input" value="{{ $applicant->first_name }} {{ $applicant->last_name }}">
            </div>
        </div>
    </div>

    <div class="student-table-wrapper applicant-content-shell applicant-content-shell-schedule">
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
