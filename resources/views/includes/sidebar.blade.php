<aside class="plp-sidebar">
    {{-- Logo + School Name --}}
    <div class="sidebar-brand">
        <img src="{{ asset('img/logobg.png') }}" alt="PLP Logo" class="sidebar-logo">
        <img src="{{ asset('img/plptextlogo.png') }}" alt="PLP Text" class="sidebar-text-logo">
    </div>

    {{-- Navigation Links --}}
    <nav class="sidebar-nav">
        <a href="{{ route('student.schedule') }}" class="sidebar-link {{ request()->routeIs('student.schedule') ? 'active' : '' }}">
            {{-- Vuesax linear/calendar --}}
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M8 2V5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M16 2V5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M3.5 9.09009H20.5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M21 8.5V17C21 20 19.5 22 16 22H8C4.5 22 3 20 3 17V8.5C3 5.5 4.5 3.5 8 3.5H16C19.5 3.5 21 5.5 21 8.5Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M11.9955 13.7H12.0045" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M8.29431 13.7H8.30329" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M8.29431 16.7H8.30329" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>Schedule</span>
        </a>

        <a href="{{ route('student.grades') }}" class="sidebar-link {{ request()->routeIs('student.grades') ? 'active' : '' }}">
            {{-- Vuesax linear/teacher --}}
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M10.05 2.53004L4.03 6.46004C2.1 7.48004 2.1 10.54 4.03 11.56L10.05 15.49C11.13 16.17 12.91 16.17 13.99 15.49L19.98 11.56C21.9 10.54 21.9 7.49004 19.98 6.47004L13.99 2.54004C12.91 1.86004 11.13 1.86004 10.05 2.53004Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M5.63 13.08L5.62 17.77C5.62 18.79 6.35 19.9 7.26 20.22L10.69 21.39C11.38 21.63 12.52 21.63 13.22 21.39L16.65 20.22C17.56 19.9 18.29 18.79 18.29 17.77V13.13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M21.4 15V9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>Grades</span>
        </a>

        <a href="{{ route('student.events') }}" class="sidebar-link {{ request()->routeIs('student.events') ? 'active' : '' }}">
            {{-- Vuesax calendar with number 8 --}}
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M8 2V5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M16 2V5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M3.5 9.09009H20.5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M21 8.5V17C21 20 19.5 22 16 22H8C4.5 22 3 20 3 17V8.5C3 5.5 4.5 3.5 8 3.5H16C19.5 3.5 21 5.5 21 8.5Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                <text x="12" y="18.5" text-anchor="middle" font-size="9" font-weight="700" fill="currentColor" stroke="none" font-family="Poppins, sans-serif">8</text>
            </svg>
            <span>Events</span>
        </a>

        <div class="sidebar-dropdown {{ request()->routeIs('student.forms.*') ? 'open' : '' }}">
            <a href="#" class="sidebar-link sidebar-dropdown-toggle {{ request()->routeIs('student.forms.*') ? 'active' : '' }}">
                {{-- Vuesax linear/document --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M14 2H6C4.9 2 4 2.89 4 4V20C4 21.1 4.9 22 6 22H18C19.1 22 20 21.1 20 20V8L14 2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M14 2V8H20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M10 12H14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M10 16H14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M10 20H14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Forms</span>
                <svg class="sidebar-chevron" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </a>

            <div class="sidebar-dropdown-menu sidebar-dropdown-menu--forms">
                <a href="{{ route('student.forms.show', 'add-change-delete') }}" class="sidebar-sublink {{ request()->is('student/forms/add-change-delete') ? 'active' : '' }}">
                    ADDING/CHANGING/DELETE form
                </a>
                <a href="{{ route('student.forms.show', 'late-leave-appeal') }}" class="sidebar-sublink {{ request()->is('student/forms/late-leave-appeal') ? 'active' : '' }}">
                    APPEAL FOR LATE APPLICATION OF LEAVE OF ABSENCE
                </a>
                <a href="{{ route('student.forms.show', 'change-grade') }}" class="sidebar-sublink {{ request()->is('student/forms/change-grade') ? 'active' : '' }}">
                    APPLICATION FOR CHANGE OF GRADE
                </a>
                <a href="{{ route('student.forms.show', 'completion-grade') }}" class="sidebar-sublink {{ request()->is('student/forms/completion-grade') ? 'active' : '' }}">
                    APPLICATION FOR COMPLETION OF GRADE
                </a>
                <a href="{{ route('student.forms.show', 'cross-enroll') }}" class="sidebar-sublink {{ request()->is('student/forms/cross-enroll') ? 'active' : '' }}">
                    APPLICATION TO CROSS ENROLL
                </a>
            </div>
        </div>
    </nav>

    {{-- Log Out --}}
    <div class="sidebar-logout">
        <a href="{{ route('logout') }}" class="sidebar-link logout-link"
           onclick="event.preventDefault(); document.getElementById('sidebar-logout-form').submit();">
            <span>Log Out</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H4.5z"/>
            </svg>
        </a>
        <form id="sidebar-logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>
</aside>
