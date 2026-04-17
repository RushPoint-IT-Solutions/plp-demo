<aside class="plp-sidebar">
    <div class="sidebar-brand">
        <img src="{{ asset('img/logobg.png') }}" alt="PLP Logo" class="sidebar-logo">
        <img src="{{ asset('img/plptextlogo.png') }}" alt="PLP Text" class="sidebar-text-logo">
    </div>

    <nav class="sidebar-nav">
        <a href="{{ route('parent.grades') }}" class="sidebar-link {{ request()->routeIs('parent.grades') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M9.02 2.84004L3.63 7.04004C2.73 7.74004 2 9.23004 2 10.36V17.77C2 20.09 3.89 21.99 6.21 21.99H17.79C20.11 21.99 22 20.09 22 17.78V10.5C22 9.29004 21.19 7.74004 20.2 7.05004L14.02 2.72004C12.62 1.74004 10.37 1.79004 9.02 2.84004Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M12 17.99V14.99" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>Grades</span>
        </a>

        <a href="{{ route('parent.student-profile') }}" class="sidebar-link {{ request()->routeIs('parent.student-profile') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <span>Student Profile</span>
        </a>

        <a href="{{ route('parent.calendar') }}" class="sidebar-link {{ request()->routeIs('parent.calendar') ? 'active' : '' }}">
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

        <a href="{{ route('parent.contact-us') }}" class="sidebar-link {{ request()->routeIs('parent.contact-us') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 16.92V19a2 2 0 0 1-2.18 2 19.87 19.87 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.87 19.87 0 0 1 2.12 3.18 2 2 0 0 1 4.11 1h2.09a2 2 0 0 1 2 1.72c.12.9.33 1.79.62 2.64a2 2 0 0 1-.45 2.11L7.1 8.75a16 16 0 0 0 6.15 6.15l1.28-1.27a2 2 0 0 1 2.11-.45c.85.29 1.74.5 2.64.62A2 2 0 0 1 22 16.92z"></path>
            </svg>
            <span>Contact Us</span>
        </a>

        <a href="{{ route('parent.change-password') }}" class="sidebar-link {{ request()->routeIs('parent.change-password') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 3l4 4-7.5 7.5H4.5V11z"></path>
                <path d="M14 5l2 2"></path>
                <path d="M20 14v5a2 2 0 0 1-2 2H8"></path>
            </svg>
            <span>Change Password</span>
        </a>
    </nav>

    <div class="sidebar-logout">
        <a href="{{ route('logout') }}" class="sidebar-link logout-link" onclick="event.preventDefault(); document.getElementById('parent-sidebar-logout-form').submit();">
            <span>Log Out</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H4.5z"/>
            </svg>
        </a>
        <form id="parent-sidebar-logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
            @csrf
        </form>
    </div>
</aside>
