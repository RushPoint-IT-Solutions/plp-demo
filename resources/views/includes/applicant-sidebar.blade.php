<aside class="plp-sidebar">
    {{-- Logo + School Name --}}
    <div class="sidebar-brand">
        <img src="{{ asset('img/logobg.png') }}" alt="PLP Logo" class="sidebar-logo">
        <img src="{{ asset('img/plptextlogo.png') }}" alt="PLP Text" class="sidebar-text-logo">
    </div>

    {{-- Navigation Links --}}
    <nav class="sidebar-nav">

        {{-- Application Form --}}
        <a href="{{ route('applicant.application-form') }}" class="sidebar-link {{ request()->routeIs('applicant.application-form') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="8" y1="8" x2="16" y2="8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="8" y1="12" x2="16" y2="12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="8" y1="16" x2="12" y2="16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>Application Form</span>
        </a>

        {{-- Schedule of Exam --}}
        <a href="{{ route('applicant.schedule-of-exam') }}" class="sidebar-link {{ request()->routeIs('applicant.schedule-of-exam') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="16" y1="2" x2="16" y2="6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="8" y1="2" x2="8" y2="6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>Schedule of Exam</span>
        </a>

        {{-- Calendar --}}
        <a href="{{ route('applicant.calendar') }}" class="sidebar-link {{ request()->routeIs('applicant.calendar') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M8 2V5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M16 2V5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M3.5 9.09009H20.5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M21 8.5V17C21 20 19.5 22 16 22H8C4.5 22 3 20 3 17V8.5C3 5.5 4.5 3.5 8 3.5H16C19.5 3.5 21 5.5 21 8.5Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M11.9955 13.7H12.0045" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M8.29431 13.7H8.30329" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M8.29431 16.7H8.30329" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>Calendar</span>
        </a>

        {{-- Exam Result --}}
        <a href="{{ route('applicant.exam-result') }}" class="sidebar-link {{ request()->routeIs('applicant.exam-result') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <line x1="18" y1="20" x2="18" y2="10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="12" y1="20" x2="12" y2="4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="6" y1="20" x2="6" y2="14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>Exam Result</span>
        </a>

        {{-- Application Status (Official Correspondence) --}}
        <a href="{{ route('applicant.correspondence') }}" class="sidebar-link {{ request()->routeIs('applicant.correspondence') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="12" y1="8" x2="12" y2="12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="12" y1="16" x2="12.01" y2="16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>Application Status</span>
        </a>

    </nav>

    {{-- Logout --}}
    <div class="sidebar-logout">
        <a href="{{ route('logout') }}" class="sidebar-link logout-link"
           onclick="event.preventDefault(); document.getElementById('applicant-logout-form').submit();">
            <span>Log Out</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H4.5z"/>
            </svg>
        </a>
        <form id="applicant-logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
            @csrf
        </form>
    </div>
</aside>
