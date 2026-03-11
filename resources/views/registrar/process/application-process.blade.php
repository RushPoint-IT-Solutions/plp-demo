@extends('layouts.registrar')

@section('title', 'PLP - Application Process')
@section('page-title', 'APPLICATION PROCESS')



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
        <a href="{{ route('logout') }}" class="sidebar-link logout-link"
           onclick="event.preventDefault(); document.getElementById('registrar-logout-form').submit();">
            <span>Log Out</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H4.5z"/>
            </svg>
        </a>
    </div>
</aside>
@endpush

@section('content')

{{-- ========== MAIN APPLICATION PROCESS VIEW ========== --}}
<div class="app-process-page" id="appProcessPage">

    {{-- Filter Bar --}}
    <div class="app-filter-bar">
        {{-- Row 1: Date range + Course --}}
        <div class="app-filter-row">
            <div class="app-filter-group" style="flex:1;">
                <span class="app-filter-label">From Date</span>
                <select class="app-filter-select" style="width:100%;">
                    <option>January 1, 2026</option>
                    <option>February 1, 2026</option>
                    <option>March 1, 2026</option>
                </select>
            </div>
            <div class="app-filter-group" style="flex:1;">
                <span class="app-filter-label">To Date</span>
                <select class="app-filter-select" style="width:100%;">
                    <option>January 1, 2026</option>
                    <option>February 1, 2026</option>
                    <option>March 1, 2026</option>
                </select>
            </div>
            <div class="app-filter-group" style="flex:2;">
                <span class="app-filter-label">Course</span>
                <select class="app-filter-select" style="width:100%;">
                    <option value="">Select Course</option>
                    <option>BSCS</option>
                    <option>BSIT</option>
                    <option>BSED</option>
                    <option>BSBA</option>
                </select>
            </div>
        </div>

        {{-- Row 2: Search + Sort By + Show Entries --}}
        <div class="app-filter-row">
            <div class="app-filter-group" style="flex:2;">
                <span class="app-filter-label">Search</span>
                <input type="text" class="app-filter-input" placeholder="Search" style="width:100%;">
            </div>
            <div class="app-filter-group" style="flex:2;">
                <span class="app-filter-label">Sort By</span>
                <input type="text" class="app-filter-input" placeholder="Applicant ID" style="width:100%;">
            </div>
            <div class="app-filter-group" style="flex:1;">
                <span class="app-filter-label">Show Entries</span>
                <select class="app-filter-select" style="width:100%;">
                    <option>100</option>
                    <option>50</option>
                    <option>25</option>
                    <option>10</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="app-table-wrap table-responsive">
        <table class="app-table">
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
                <tr data-id="2223A8137" data-name="Mark Jay Bares">
                    <td>1</td>
                    <td>2223A8137</td>
                    <td>Mark Jay Bares</td>
                    <td>BSCS</td>
                    <td>June 07, 2025</td>
                    <td>June 07, 2025</td>
                    <td><span class="app-status-accepted"><span class="app-status-dot"></span>Accepted</span></td>
                </tr>
                <tr data-id="2223A8138" data-name="Andrea Jane Austero">
                    <td>2</td>
                    <td>2223A8138</td>
                    <td>Andrea Jane Austero</td>
                    <td>BSCS</td>
                    <td>June 07, 2025</td>
                    <td>June 07, 2025</td>
                    <td><span class="app-status-accepted"><span class="app-status-dot"></span>Accepted</span></td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

{{-- ========== APPLICANT DETAIL VIEW ========== --}}
<div id="applicantDetailView">

    {{-- Applicant ID + Name --}}
    <div class="appl-detail-header">
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
        <div class="under-dev-notice">Under Development</div>
    </div>

    {{-- Documents Submitted --}}
    <div class="applicant-panel" id="panel-documents-submitted">
        <div class="under-dev-notice">Under Development</div>
    </div>

    {{-- Schedule of Exam --}}
    <div class="applicant-panel" id="panel-schedule-exam">
        <div class="sched-exam-card">
            <div class="sched-fields-row">
                <div class="sched-field-group">
                    <label>Date</label>
                    <select>
                        <option value="">MM-DD-YYYY</option>
                    </select>
                </div>
                <div class="sched-field-group">
                    <label>Time</label>
                    <select>
                        <option value="">00:00 AM</option>
                        <option>07:00 AM</option>
                        <option>08:00 AM</option>
                        <option>09:00 AM</option>
                        <option>10:00 AM</option>
                        <option>01:00 PM</option>
                        <option>02:00 PM</option>
                        <option>03:00 PM</option>
                    </select>
                </div>
                <div class="sched-field-group flex-grow">
                    <label>Venue</label>
                    <input type="text" placeholder="Room #123">
                </div>
            </div>
            <div class="sched-actions">
                <button type="button" class="sched-btn-save">Save</button>
                <button type="button" class="sched-btn-print">Print</button>
            </div>
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
        <div class="under-dev-notice">Under Development</div>
    </div>

    {{-- Exam Result --}}
    <div class="applicant-panel" id="panel-exam-result">
        <div class="under-dev-notice">Under Development</div>
    </div>

    {{-- Approval --}}
    <div class="applicant-panel" id="panel-approval">
        <div class="under-dev-notice">Under Development</div>
    </div>

    {{-- Application Status --}}
    <div class="applicant-panel" id="panel-application-status">
        <div class="under-dev-notice">Under Development</div>
    </div>

</div>

@endsection

@push('scripts')
<script src="{{ asset('js/application-process.js') }}?v={{ time() }}"></script>
@endpush
