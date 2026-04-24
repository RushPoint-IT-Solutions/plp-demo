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

@push('styles')
<style>
    /* Schedule of Exam Responsive Styles */
    .sched-exam-form-row {
        display: flex;
        align-items: flex-end;
        gap: 15px;
        margin-bottom: 25px;
        padding-bottom: 20px;
        border-bottom: 1px solid #f1f5f9;
    }
    .sched-form-group {
        flex: 1;
    }
    .sched-form-group--venue {
        flex: 1.5;
    }
    .sched-form-group label {
        font-size: 0.75rem;
        font-weight: 800;
        color: #006837;
        text-transform: uppercase;
        margin-bottom: 6px;
        display: block;
    }
    .sched-form-group input {
        height: 40px;
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 0 12px;
        font-size: 0.9rem;
        background-color: #f8fafc;
    }
    .sched-form-actions {
        display: flex;
        gap: 10px;
    }
    .sched-form-actions button {
        height: 40px;
        padding: 0 25px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    .sched-btn-save {
        background: #006837;
        color: #fff;
        border: none;
    }
    .sched-btn-save:hover {
        background: #004d29;
    }
    .sched-btn-print {
        background: #fff;
        color: #006837;
        border: 1.5px solid #006837;
    }
    .sched-btn-print:hover {
        background: #f0fdf4;
    }

    @media (max-width: 991px) {
        .sched-exam-form-row {
            flex-direction: column;
            align-items: stretch;
            gap: 15px;
        }
        .sched-form-group--venue {
            flex: none;
        }
        .sched-form-actions {
            margin-top: 5px;
        }
        .sched-form-actions button {
            flex: 1;
        }
    }
</style>
@endpush

<div class="app-process-page applicant-consistent-page">
    <div class="app-filter-bar">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="app-filter-group app-filter-group--compact">
                    <label class="app-filter-label">Applicant ID</label>
                    <input type="text" class="app-filter-input w-100" value="{{ $applicant->applicant_id }}" readonly>
                </div>
            </div>
            <div class="col-md-8">
                <div class="app-filter-group app-filter-group--compact">
                    <label class="app-filter-label">Applicant Name</label>
                    <input type="text" class="app-filter-input w-100" value="{{ $applicant->first_name }} {{ $applicant->last_name }}" readonly>
                </div>
            </div>
        </div>
    </div>

    <div class="sched-exam-card">
        <div class="sched-exam-form-row">
            <div class="sched-form-group">
                <label>Date</label>
                <input type="text" value="{{ $examDateValue }}" readonly>
            </div>
            <div class="sched-form-group">
                <label>Time</label>
                <input type="text" value="{{ $examTimeValue }}" readonly>
            </div>
            <div class="sched-form-group sched-form-group--venue">
                <label>Venue</label>
                <input type="text" value="{{ $examRoomValue }}" placeholder="To be announced" readonly>
            </div>
            <div class="sched-form-actions">
                <button type="button" class="sched-btn-save" id="applicantSaveExamBtn">Save</button>
                <button type="button" class="sched-btn-print" id="applicantPrintExamBtn">Print</button>
            </div>
        </div>

        <div class="sched-reminders">
            @if(!$applicant->exam_date)
                <div class="applicant-schedule-empty mb-4 p-3 border rounded text-center" style="background: #fff8f8; border-color: #fee2e2; color: #b91c1c;">
                    <p class="mb-0"><strong>Notice:</strong> There is no schedule of exam yet. Please check back later.</p>
                </div>
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
