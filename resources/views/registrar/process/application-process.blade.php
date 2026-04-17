@extends('layouts.registrar')

@section('title', 'PLP - Application List')
@section('page-title', 'APPLICATION LIST')



{{-- ====== Applicant Detail Sidebar (injected into layout's extra-sidebar slot) ====== --}}
@push('extra-sidebar')
<aside class="plp-sidebar" id="applicantSidebar">
    <div class="sidebar-brand">
        <img src="{{ asset('img/logobg.png') }}" alt="PLP Logo" class="sidebar-logo">
        <img src="{{ asset('img/plptextlogo.png') }}" alt="PLP Text" class="sidebar-text-logo">
    </div>

    <nav class="sidebar-nav">
        <button class="appl-sidebar-back" id="backToMainBtn" type="button">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            <span>Main</span>
        </button>

        <a href="#" class="sidebar-link applicant-nav-link" data-panel="application-form" data-title="APPLICATION FORM">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="18" height="18" rx="2"/><line x1="8" y1="8" x2="16" y2="8"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="8" y1="16" x2="12" y2="16"/>
            </svg>
            <span>Application Form</span>
        </a>

        <a href="#" class="sidebar-link applicant-nav-link" data-panel="documents-submitted" data-title="DOCUMENTS SUBMITTED">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="12" y2="17"/>
            </svg>
            <span>Documents Submitted</span>
        </a>

        <a href="#" class="sidebar-link applicant-nav-link" data-panel="schedule-exam" data-title="SCHEDULE OF EXAM">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            <span>Schedule of Exam</span>
        </a>

        <a href="#" class="sidebar-link applicant-nav-link" data-panel="medical-clearance" data-title="MEDICAL CLEARANCE">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
            </svg>
            <span>Medical Clearance</span>
        </a>

        <a href="#" class="sidebar-link applicant-nav-link" data-panel="exam-result" data-title="EXAM RESULT">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>
            </svg>
            <span>Exam Result</span>
        </a>

        <a href="#" class="sidebar-link applicant-nav-link" data-panel="approval" data-title="APPROVAL">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <span>Approval</span>
        </a>

        <a href="#" class="sidebar-link applicant-nav-link" data-panel="application-status" data-title="APPLICATION STATUS">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span>Application Status</span>
        </a>
    </nav>

    <div class="sidebar-logout">
        <form action="{{ route('logout') }}" method="POST" class="sidebar-logout-form">
            @csrf
            <button type="submit" class="sidebar-link logout-link">
                <span>Log Out</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H4.5z"/>
                </svg>
            </button>
        </form>
    </div>
</aside>
@endpush

@section('content')

@php
    $filters = isset($filters) && is_array($filters) ? $filters : [
        'from_date' => null,
        'to_date' => null,
        'course_id' => 0,
        'search' => '',
        'sort_by' => 'date_updated',
        'sort_direction' => 'desc',
        'per_page' => 10,
    ];
    $courses = isset($courses) ? $courses : collect();
@endphp

{{-- ========== MAIN APPLICATION PROCESS VIEW ========== --}}
<div
    class="app-process-page"
    id="appProcessPage"
    data-form-url-template="{{ route('registrar.process.application.form.edit', ['applicant' => '__APPLICANT_ID__']) }}"
    data-exam-schedule-url-template="{{ route('registrar.process.application.exam-schedule.update', ['applicant' => '__APPLICANT_ID__']) }}"
    data-exam-result-url-template="{{ route('registrar.process.application.exam-result.update', ['applicant' => '__APPLICANT_ID__']) }}"
    data-approval-status-url-template="{{ route('registrar.process.application.approval-status.update', ['applicant' => '__APPLICANT_ID__']) }}"
    data-csrf-token="{{ csrf_token() }}"
>

    {{-- Filter Bar --}}
    <form class="app-filter-bar" method="GET" action="{{ route('registrar.process.application') }}" id="applicationFilterForm">
        {{-- Row 1: Date range + Course --}}
        <div class="app-filter-row">
            <div class="app-filter-group">
                <label class="app-filter-label" for="appFromDate">From Date</label>
                <input
                    id="appFromDate"
                    name="from_date"
                    type="date"
                    class="app-filter-input w-100"
                    value="{{ $filters['from_date'] }}"
                >
            </div>
            <div class="app-filter-group">
                <label class="app-filter-label" for="appToDate">To Date</label>
                <input
                    id="appToDate"
                    name="to_date"
                    type="date"
                    class="app-filter-input w-100"
                    value="{{ $filters['to_date'] }}"
                >
            </div>
            <div class="app-filter-group app-filter-select-wide">
                <label class="app-filter-label" for="appCourse">Course</label>
                <select id="appCourse" name="course_id" class="app-filter-select w-100">
                    <option value="">All Courses</option>
                    @foreach($courses as $course)
                        @php
                            $courseText = trim(((string) ($course->code ?: '')) . ' - ' . ((string) ($course->name ?: '')));
                        @endphp
                        <option value="{{ $course->id }}" {{ (int) $filters['course_id'] === (int) $course->id ? 'selected' : '' }}>
                            {{ $courseText !== '-' ? $courseText : ('Course #' . $course->id) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Row 2: Search + Sort + Entries --}}
        <div class="app-filter-row">
            <div class="app-filter-group app-filter-select-wide">
                <label class="app-filter-label" for="appSearch">Search</label>
                <input
                    id="appSearch"
                    name="search"
                    type="text"
                    class="app-filter-input w-100"
                    placeholder="Search applicant ID or name"
                    value="{{ $filters['search'] }}"
                >
            </div>
            <div class="app-filter-group">
                <label class="app-filter-label" for="appSortBy">Sort By</label>
                <select id="appSortBy" name="sort_by" class="app-filter-select w-100">
                    <option value="applicant_id" {{ $filters['sort_by'] === 'applicant_id' ? 'selected' : '' }}>Applicant ID</option>
                    <option value="applicant_name" {{ $filters['sort_by'] === 'applicant_name' ? 'selected' : '' }}>Applicant Name</option>
                    <option value="date_applied" {{ $filters['sort_by'] === 'date_applied' ? 'selected' : '' }}>Date Applied</option>
                    <option value="date_updated" {{ $filters['sort_by'] === 'date_updated' ? 'selected' : '' }}>Date Last Update</option>
                </select>
            </div>
            <div class="app-filter-group app-filter-select-sm">
                <label class="app-filter-label" for="appSortDirection">Order</label>
                <select id="appSortDirection" name="sort_direction" class="app-filter-select w-100">
                    <option value="asc" {{ $filters['sort_direction'] === 'asc' ? 'selected' : '' }}>Ascending</option>
                    <option value="desc" {{ $filters['sort_direction'] === 'desc' ? 'selected' : '' }}>Descending</option>
                </select>
            </div>
            <div class="app-filter-group app-filter-select-sm">
                <label class="app-filter-label" for="appPerPage">Show Entries</label>
                <select id="appPerPage" name="per_page" class="app-filter-select w-100">
                    @foreach([10, 25, 50, 100] as $perPageOption)
                        <option value="{{ $perPageOption }}" {{ (int) $filters['per_page'] === (int) $perPageOption ? 'selected' : '' }}>{{ $perPageOption }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="app-filter-row app-filter-actions-row">
            <p class="app-filter-auto-note">Filters auto-apply as you change values or type in search.</p>
            <div class="app-filter-actions-group">
                <a href="{{ route('registrar.process.application') }}" class="app-filter-action-btn app-filter-action-btn--reset">Reset</a>
                <a
                    href="{{ route('registrar.process.application.print', array_merge(request()->query(), ['autoprint' => 1])) }}"
                    target="_blank"
                    rel="noopener"
                    class="app-filter-action-btn app-filter-action-btn--print"
                    id="printApplicantListBtn"
                >
                    Print List
                </a>
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="app-table-wrap table-responsive">
        <table class="app-table" data-no-auto-pager="1">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Applicant ID</th>
                    <th>Name</th>
                    <th>Program</th>
                    <th>Date Applied</th>
                    <th>Date Last Update</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($applicants as $index => $applicant)
                @php
                    $displayName = trim((string) $applicant->first_name . ' ' . (string) $applicant->last_name);
                    $preference = optional($applicant->applicationPreference);
                    $preferredCourse = optional($preference->course);
                    $programLabel = 'N/A';
                    if ($preference->apply_program === 'college') {
                        $programLabel = $preferredCourse->name ?: ($preferredCourse->code ?: 'College');
                    } elseif ($preference->apply_program === 'senior_high') {
                        $programLabel = $preference->apply_strand ?: 'Senior High';
                    }

                    $rawApplicationStatus = strtolower(trim((string) ($applicant->application_status ?: 'in process')));
                    $statusRaw = 'In Process';
                    if ($rawApplicationStatus === 'submitted' || $rawApplicationStatus === 'document submitted') {
                        $statusRaw = 'Document Submitted';
                    } elseif ($rawApplicationStatus === 'on probation' || $rawApplicationStatus === 'on_probation') {
                        $statusRaw = 'On Probation';
                    } elseif ($rawApplicationStatus === 'in process' || $rawApplicationStatus === 'in_process') {
                        $statusRaw = 'In Process';
                    } elseif ($rawApplicationStatus === 'rejected') {
                        $statusRaw = 'Rejected';
                    } elseif ($rawApplicationStatus === 'incomplete' || $rawApplicationStatus === 'draft') {
                        $statusRaw = 'Incomplete';
                    } elseif ($rawApplicationStatus === 'accepted') {
                        $statusRaw = 'Accepted';
                    }

                    $statusClass = 'app-status-pending';
                    if ($statusRaw === 'Accepted' || $statusRaw === 'Document Submitted' || $statusRaw === 'In Process') {
                        $statusClass = 'app-status-accepted';
                    }
                    if ($statusRaw === 'Rejected') {
                        $statusClass = 'app-status-rejected';
                    }

                    $dateApplied = $applicant->application_submitted_at ?: $applicant->created_at;
                @endphp
                <tr
                    data-pk="{{ $applicant->id }}"
                    data-id="{{ $applicant->applicant_id }}"
                    data-name="{{ e($displayName) }}"
                    data-program="{{ e($programLabel) }}"
                    data-application-status="{{ e($statusRaw) }}"
                    data-exam-date="{{ optional($applicant->exam_date)->format('Y-m-d') }}"
                    data-exam-time="{{ optional($applicant->exam_date)->format('H:i') }}"
                    data-exam-room="{{ e((string) ($applicant->exam_room ?? '')) }}"
                    data-exam-result-status="{{ e((string) ($applicant->exam_result_status ?: 'Pending')) }}"
                    data-exam-score="{{ $applicant->exam_score !== null ? $applicant->exam_score : '' }}"
                >
                    <td>{{ ($applicants->firstItem() ?? 1) + $index }}</td>
                    <td>{{ $applicant->applicant_id }}</td>
                    <td>{{ $displayName ?: 'N/A' }}</td>
                    <td>{{ $programLabel }}</td>
                    <td>{{ optional($dateApplied)->format('M d, Y') ?: 'N/A' }}</td>
                    <td>{{ optional($applicant->updated_at)->format('M d, Y') ?: 'N/A' }}</td>
                    <td class="js-application-status"><span class="{{ $statusClass }}"><span class="app-status-dot"></span>{{ strtoupper($statusRaw) }}</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">No applicants found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="app-table-pager">
        {{ $applicants->links() }}
    </div>

</div>

{{-- ========== APPLICANT DETAIL VIEW ========== --}}
<div id="applicantDetailView">

    {{-- Applicant ID + Name --}}
    <div class="appl-detail-header">
        <input type="hidden" id="detailApplicantPk">
        <div class="appl-detail-field">
            <label>Applicant ID</label>
            <input type="text" id="detailApplicantId" readonly>
        </div>
        <div class="appl-detail-field wide">
            <label>Applicant Name</label>
            <input type="text" id="detailApplicantName" readonly>
        </div>
    </div>

    {{-- Application Form --}}
    <div class="applicant-panel" id="panel-application-form">
        @include('registrar.process.panels.application-form')
    </div>

    {{-- Documents Submitted --}}
    <div class="applicant-panel" id="panel-documents-submitted">
        @include('registrar.process.panels.documents-submitted')
    </div>

    {{-- Schedule of Exam --}}
    <div class="applicant-panel" id="panel-schedule-exam">
        <div class="sched-exam-card">
            <div class="sched-fields-row">
                <div class="sched-field-group">
                    <label>Date</label>
                    <input type="date" id="scheduleExamDate" class="app-filter-input w-100">
                </div>
                <div class="sched-field-group">
                    <label>Time</label>
                    <input type="time" id="scheduleExamTime" class="app-filter-input w-100">
                </div>
                <div class="sched-field-group sched-field-group--venue">
                    <label>Venue</label>
                    <input type="text" id="scheduleExamVenue" placeholder="Room #123">
                </div>
            </div>
            <div class="sched-actions">
                <button type="button" class="sched-btn-save" id="saveExamScheduleBtn">Save</button>
                <button type="button" class="sched-btn-print" id="printExamScheduleBtn">Print</button>
            </div>
            <div id="scheduleExamFeedback" class="schedule-exam-feedback"></div>
            <div class="sched-reminders">
                <p><strong>REMINDERS:</strong></p>
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
                <p><strong>DRESS CODE</strong></p>
                <ul>
                    <li>Wear a white top, polo shirt, or blouse.</li>
                    <li>Avoid wearing tattered, torn, or ripped jeans or pants.</li>
                    <li>Shorts and mini skirts are not permitted.</li>
                    <li>Opt for closed, comfortable shoes.</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Medical Clearance --}}
    <div class="applicant-panel" id="panel-medical-clearance">
        @include('registrar.process.panels.medical-clearance')
    </div>

    {{-- Exam Result --}}
    <div class="applicant-panel" id="panel-exam-result">
        <div class="student-table-wrapper applicant-content-shell">
            <div class="applicant-result-box is-hidden" id="examResultCard">
                <div class="result-status-badge" id="examResultBadge">PENDING</div>
                <p class="result-score is-hidden" id="examResultScoreLine">Score: <strong id="examResultScoreText"></strong></p>
                <p class="result-score result-score-message" id="examResultMessage"></p>
            </div>
            <div class="applicant-no-result" id="examResultNoData">No exam result is available yet.</div>
        </div>
    </div>

    {{-- Approval --}}
    <div class="applicant-panel" id="panel-approval">
        @include('registrar.process.panels.approval')
    </div>

    {{-- Application Status --}}
    <div class="applicant-panel" id="panel-application-status">
        @include('registrar.process.panels.application-status')
    </div>

</div>

@endsection

@push('scripts')
<script src="{{ asset('js/application-process.js') }}?v={{ time() }}"></script>
@endpush
