<aside class="plp-sidebar" id="applicantSidebar">
    {{-- Logo --}}
    <div class="sidebar-brand">
        <img src="{{ asset('img/logobg.png') }}" alt="PLP Logo" class="sidebar-logo">
        <img src="{{ asset('img/plptextlogo.png') }}" alt="PLP Text" class="sidebar-text-logo">
    </div>

    <nav class="sidebar-nav">
        {{-- Back to Main --}}
        <button class="appl-sidebar-back" id="backToMainBtn" type="button">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            <span>Main</span>
        </button>

        {{-- Application Form --}}
        <a href="#" class="sidebar-link applicant-nav-link" data-panel="application-form" data-title="APPLICATION FORM">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="18" height="18" rx="2"/>
                <line x1="8" y1="8" x2="16" y2="8"/>
                <line x1="8" y1="12" x2="16" y2="12"/>
                <line x1="8" y1="16" x2="12" y2="16"/>
            </svg>
            <span>Application Form</span>
        </a>

        {{-- Documents Submitted --}}
        <a href="#" class="sidebar-link applicant-nav-link" data-panel="documents-submitted" data-title="DOCUMENTS SUBMITTED">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="9" y1="13" x2="15" y2="13"/>
                <line x1="9" y1="17" x2="12" y2="17"/>
            </svg>
            <span>Documents Submitted</span>
        </a>

        {{-- Schedule of Exam --}}
        <a href="#" class="sidebar-link applicant-nav-link" data-panel="schedule-exam" data-title="SCHEDULE OF EXAM">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            <span>Schedule of Exam</span>
        </a>

        {{-- Medical Clearance --}}
        <a href="#" class="sidebar-link applicant-nav-link" data-panel="medical-clearance" data-title="MEDICAL CLEARANCE">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
            </svg>
            <span>Medical Clearance</span>
        </a>

        {{-- Exam Result --}}
        <a href="#" class="sidebar-link applicant-nav-link" data-panel="exam-result" data-title="EXAM RESULT">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="20" x2="18" y2="10"/>
                <line x1="12" y1="20" x2="12" y2="4"/>
                <line x1="6" y1="20" x2="6" y2="14"/>
            </svg>
            <span>Exam Result</span>
        </a>

        {{-- Approval --}}
        <a href="#" class="sidebar-link applicant-nav-link" data-panel="approval" data-title="APPROVAL">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <span>Approval</span>
        </a>

        {{-- Application Status --}}
        <a href="#" class="sidebar-link applicant-nav-link" data-panel="application-status" data-title="APPLICATION STATUS">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span>Application Status</span>
        </a>
    </nav>

    {{-- Log Out --}}
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
