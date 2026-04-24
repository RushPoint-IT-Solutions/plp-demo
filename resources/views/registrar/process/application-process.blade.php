@extends('layouts.registrar')

@section('title', 'PLP - Application List')
@section('page-title', 'APPLICATION LIST')

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/flatpickr/flatpickr.min.css') }}">
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



{{-- ====== Applicant Detail Sidebar (injected into layout's extra-sidebar slot) ====== --}}
@push('extra-sidebar')
<aside class="plp-sidebar" id="applicantSidebar">
    <div class="sidebar-brand">
        <img src="{{ asset('img/logobg.png') }}" alt="PLP Logo" class="sidebar-logo">
        <img src="{{ asset('img/plptextlogo.png') }}" alt="PLP Text" class="sidebar-text-logo">
    </div>

    <nav class="sidebar-nav">
        <button class="sidebar-link appl-sidebar-back" id="backToMainBtn" type="button">
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
        <a href="{{ route('logout') }}" class="sidebar-link logout-link js-registrar-logout">
            <span>Log Out</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H4.5z"/>
            </svg>
        </a>
        <form id="registrar-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
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

    $courseOptions = collect($courses)->map(function ($course) {
        $courseText = trim(((string) ($course->code ?: '')) . ' - ' . ((string) ($course->name ?: '')));

        return [
            'value' => (string) $course->id,
            'label' => $courseText !== '-' ? $courseText : ('Course #' . $course->id),
        ];
    })->prepend([
        'value' => '',
        'label' => 'All Courses',
    ])->values()->all();

    $sortByOptions = [
        ['value' => 'applicant_id', 'label' => 'Applicant ID'],
        ['value' => 'applicant_name', 'label' => 'Applicant Name'],
        ['value' => 'date_applied', 'label' => 'Date Applied'],
        ['value' => 'date_updated', 'label' => 'Date Last Update'],
    ];

    $sortDirectionOptions = [
        ['value' => 'asc', 'label' => 'Ascending'],
        ['value' => 'desc', 'label' => 'Descending'],
    ];

    $perPageOptions = collect([10, 25, 50, 100])->map(function ($value) {
        return [
            'value' => (string) $value,
            'label' => (string) $value,
        ];
    })->values()->all();
@endphp

{{-- ========== MAIN APPLICATION PROCESS VIEW ========== --}}
<div
    class="app-process-page"
    id="appProcessPage"
    data-form-url-template="{{ route('registrar.process.application.form.edit', ['applicant' => '__APPLICANT_ID__']) }}"
    data-exam-schedule-url-template="{{ route('registrar.process.application.exam-schedule.update', ['applicant' => '__APPLICANT_ID__']) }}"
    data-exam-result-url-template="{{ route('registrar.process.application.exam-result.update', ['applicant' => '__APPLICANT_ID__']) }}"
    data-approval-status-url-template="{{ route('registrar.process.application.approval-status.update', ['applicant' => '__APPLICANT_ID__']) }}"
    data-documents-data-url-template="{{ route('registrar.process.application.documents.data', ['applicant' => '__APPLICANT_ID__']) }}"
    data-documents-upsert-url-template="{{ route('registrar.process.application.documents.upsert', ['applicant' => '__APPLICANT_ID__', 'registrarRequirement' => '__REQUIREMENT_ID__']) }}"
    data-medical-data-url-template="{{ route('registrar.process.application.documents.data', ['applicant' => '__APPLICANT_ID__']) }}"
    data-medical-upsert-url-template="{{ route('registrar.process.application.documents.upsert', ['applicant' => '__APPLICANT_ID__', 'registrarRequirement' => '__REQUIREMENT_ID__']) }}"
    data-print-url="{{ route('registrar.process.application.print') }}"
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
                @include('registrar.components.listbox-select', [
                    'id' => 'appCourse',
                    'name' => 'course_id',
                    'options' => $courseOptions,
                    'selected' => (string) ($filters['course_id'] ?: ''),
                    'placeholder' => 'All Courses'
                ])
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
                @include('registrar.components.listbox-select', [
                    'id' => 'appSortBy',
                    'name' => 'sort_by',
                    'options' => $sortByOptions,
                    'selected' => (string) $filters['sort_by'],
                    'placeholder' => 'Sort By'
                ])
            </div>
            <div class="app-filter-group app-filter-select-sm">
                <label class="app-filter-label" for="appSortDirection">Order</label>
                @include('registrar.components.listbox-select', [
                    'id' => 'appSortDirection',
                    'name' => 'sort_direction',
                    'options' => $sortDirectionOptions,
                    'selected' => (string) $filters['sort_direction'],
                    'placeholder' => 'Order'
                ])
            </div>
            <div class="app-filter-group app-filter-select-sm">
                <label class="app-filter-label" for="appPerPage">Show Entries</label>
                @include('registrar.components.listbox-select', [
                    'id' => 'appPerPage',
                    'name' => 'per_page',
                    'options' => $perPageOptions,
                    'selected' => (string) ((int) $filters['per_page']),
                    'placeholder' => '10'
                ])
            </div>
        </div>

        <div class="app-filter-row app-filter-actions-row">
            <p class="app-filter-auto-note">Filters auto-apply as you change values; the search field applies after a short pause, or press Enter / click Search to apply it immediately.</p>
            <div class="app-filter-actions-group">
                <button type="button" class="app-filter-action-btn app-filter-action-btn--print" id="searchApplicantListBtn">Search</button>
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
            <tbody id="applicantTableBody">
                @include('registrar.process.partials.application-process-table-rows', ['applicants' => $applicants])
            </tbody>
        </table>
    </div>

    <div class="app-table-pager" id="applicantTablePager">
        {{ $applicants->links() }}
    </div>

</div>

{{-- ========== APPLICANT DETAIL VIEW ========== --}}
<div id="applicantDetailView" style="display: none; overflow: hidden;">

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
            <div class="sched-exam-form-row">
                <div class="sched-form-group">
                    <label>EXAM DATE</label>
                    <input type="date" id="scheduleExamDate" class="app-filter-input">
                </div>
                <div class="sched-form-group">
                    <label>TIME</label>
                    <input type="time" id="scheduleExamTime" class="app-filter-input">
                </div>
                <div class="sched-form-group sched-form-group--venue">
                    <label>VENUE</label>
                    <input type="text" id="scheduleExamVenue" placeholder="Enter venue location...">
                </div>
                <div class="sched-form-actions">
                    <button type="button" class="sched-btn-save" id="saveExamScheduleBtn">Save</button>
                    <button type="button" class="sched-btn-print" id="printExamScheduleBtn">Print</button>
                </div>
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

    <div class="modal fade" id="scheduleExamSuccessModal" tabindex="-1" role="dialog" aria-labelledby="scheduleExamSuccessTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered schedule-exam-success-modal__dialog" role="document">
            <div class="modal-content schedule-exam-success-modal__content">
                <div class="modal-header schedule-exam-success-modal__header">
                    <div class="schedule-exam-success-modal__header-copy">
                        <p class="schedule-exam-success-modal__eyebrow mb-1">Success</p>
                        <h5 class="modal-title" id="scheduleExamSuccessTitle">Schedule Saved</h5>
                    </div>
                    <button type="button" class="close schedule-exam-success-modal__close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body schedule-exam-success-modal__body">
                    <div class="schedule-exam-success-modal__hero">
                        <div class="schedule-exam-success-modal__icon" aria-hidden="true">
                            <span>&#10003;</span>
                        </div>
                        <div class="schedule-exam-success-modal__copy">
                            <p id="scheduleExamSuccessMessage" class="schedule-exam-success-modal__message mb-0">Exam schedule saved successfully.</p>
                            <p class="schedule-exam-success-modal__subtext mb-0">The applicant record now reflects the new exam date, time, and venue.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer schedule-exam-success-modal__footer">
                    <button type="button" class="apc-btn apc-btn--save schedule-exam-success-modal__confirm" data-dismiss="modal" data-bs-dismiss="modal">OK</button>
                </div>
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
<script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
<script src="{{ asset('js/registrar-listbox-select.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/application-process.js') }}?v={{ time() }}"></script>
@endpush
@push('scripts')
<script>
    // Auto-resize the application form iframe to fit its content
    (function() {
        let lastHeight = 0;
        function resizeAppFormIframe() {
            const iframe = document.getElementById('registrarAppFormEditorFrame');
            if (!iframe || iframe.style.display === 'none') return;

            try {
                const doc = iframe.contentWindow.document;
                // Use the most stable height property
                const newHeight = Math.max(
                    doc.body.scrollHeight, 
                    doc.documentElement.scrollHeight,
                    doc.body.offsetHeight,
                    doc.documentElement.offsetHeight
                );

                // Only update if height changed by a significant amount (10px)
                // and NEVER grow infinitely - check if the new height is actually different from current style
                const currentStyleHeight = parseInt(iframe.style.height) || 0;
                
                if (Math.abs(newHeight - lastHeight) > 10) {
                    // Set height exactly to content height to avoid "buffer growth" loops
                    iframe.style.height = newHeight + 'px';
                    lastHeight = newHeight;
                }
            } catch(e) {}
        }

        // Check less frequently to avoid layout thrashing
        const resizeInterval = setInterval(resizeAppFormIframe, 2000);

        const appFormFrame = document.getElementById('registrarAppFormEditorFrame');
        if (appFormFrame) {
            appFormFrame.onload = function() {
                lastHeight = 0;
                setTimeout(resizeAppFormIframe, 500);
            };
        }
        
        window.addEventListener('unload', () => clearInterval(resizeInterval));
    })();
</script>
@endpush
