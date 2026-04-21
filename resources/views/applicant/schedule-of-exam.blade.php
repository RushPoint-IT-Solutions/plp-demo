@extends(!empty($applicationFormEmbedded) ? 'layouts.applicant-embedded' : 'layouts.applicant')

@section('title', 'PLP - Schedule of Exam')
@section('page-title', 'SCHEDULE OF EXAM')

@section('content')
@php
    $hasExamSchedule = !empty($applicant->exam_date);
    $examDateValue = $hasExamSchedule ? optional($applicant->exam_date)->format('F d, Y') : 'To be announced';
    $examTimeValue = $hasExamSchedule ? optional($applicant->exam_date)->format('h:i A') : 'To be announced';
    $examRoomValue = $applicant->exam_room ?: '';
@endphp
<div class="app-process-page applicant-consistent-page">
    <div class="app-filter-bar">
        <div class="app-filter-row applicant-identity-row">
            <div class="app-filter-group app-filter-group--compact">
                <label class="app-filter-label">Applicant ID</label>
                <input type="text" class="app-filter-input" value="{{ $applicant->applicant_id }}" readonly>
            </div>
            <div class="app-filter-group app-filter-group--compact app-filter-group--wide">
                <label class="app-filter-label">Applicant Name</label>
                <input type="text" class="app-filter-input" value="{{ $applicant->first_name }} {{ $applicant->last_name }}" readonly>
            </div>
        </div>
    </div>

    <div class="sched-exam-card">
        <div class="sched-fields-row">
            <div class="sched-field-group">
                <label>Date</label>
                <input type="text" class="app-filter-input" style="width:100%;" value="{{ $examDateValue }}" readonly>
            </div>
            <div class="sched-field-group">
                <label>Time</label>
                <input type="text" class="app-filter-input" style="width:100%;" value="{{ $examTimeValue }}" readonly>
            </div>
            <div class="sched-field-group sched-field-group--venue">
                <label>Venue</label>
                <input type="text" value="{{ $examRoomValue }}" placeholder="Room #123" readonly>
            </div>
        </div>

        <div class="sched-reminders">
            <div class="sched-actions">
                <button type="button" class="sched-btn-save" id="applicantSaveExamBtn">Save</button>
                <button type="button" class="sched-btn-print" id="applicantPrintExamBtn">Print</button>
            </div>

            @if(!$applicant->exam_date)
                <p class="applicant-schedule-empty">There is no schedule of exam yet.</p>
            @endif

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
