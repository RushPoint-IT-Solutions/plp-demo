<aside class="plp-sidebar">
    {{-- Logo + School Name --}}
    <div class="sidebar-brand">
        <img src="{{ asset('img/logobg.png') }}" alt="PLP Logo" class="sidebar-logo">
        <img src="{{ asset('img/plptextlogo.png') }}" alt="PLP Text" class="sidebar-text-logo">
    </div>

    {{-- Navigation Links --}}
    <nav class="sidebar-nav">

        {{-- Faculty Load --}}
        <a href="{{ route('faculty.load') }}" class="sidebar-link {{ request()->routeIs('faculty.load') ? 'active' : '' }}">
            {{-- Vuesax linear/home-2 --}}
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M9.02 2.84004L3.63 7.04004C2.73 7.74004 2 9.23004 2 10.36V17.77C2 20.09 3.89 21.99 6.21 21.99H17.79C20.11 21.99 22 20.09 22 17.78V10.5C22 9.29004 21.19 7.74004 20.2 7.05004L14.02 2.72004C12.62 1.74004 10.37 1.79004 9.02 2.84004Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M12 17.99V14.99" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>Faculty Load</span>
        </a>

        {{-- Class List --}}
        <a href="{{ route('faculty.class-list') }}" class="sidebar-link {{ request()->routeIs('faculty.class-list') ? 'active' : '' }}">
            {{-- Vuesax linear/people --}}
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M9 2C6.38 2 4.25 4.13 4.25 6.75C4.25 9.32 6.26 11.4 8.88 11.49C8.96 11.48 9.04 11.48 9.1 11.49C9.12 11.49 9.13 11.49 9.15 11.49C9.16 11.49 9.16 11.49 9.17 11.49C11.73 11.4 13.74 9.32 13.75 6.75C13.75 4.13 11.62 2 9 2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M16.49 4C18.56 4 20.24 5.69 20.24 7.75C20.24 9.77 18.63 11.43 16.63 11.5C16.55 11.49 16.46 11.49 16.38 11.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M4.16 14.56C1.56 16.26 1.56 19.02 4.16 20.71C7.11 22.63 11.9 22.63 14.85 20.71C17.45 19.01 17.45 16.25 14.85 14.56C11.91 12.65 7.12 12.65 4.16 14.56Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M18.34 20C19.06 19.85 19.74 19.56 20.3 19.13C21.86 17.96 21.86 16.03 20.3 14.86C19.75 14.44 19.08 14.16 18.37 14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>Class List</span>
        </a>

        {{-- Calendar --}}
        <a href="{{ route('faculty.calendar') }}" class="sidebar-link {{ request()->routeIs('faculty.calendar') ? 'active' : '' }}">
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
            <span>Calendar</span>
        </a>

        {{-- Grading Sheet --}}
        <a href="{{ route('faculty.grading-sheet') }}" class="sidebar-link {{ request()->routeIs('faculty.grading-sheet') ? 'active' : '' }}">
            {{-- Vuesax linear/document-text --}}
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M21 7V17C21 20 19.5 22 16 22H8C4.5 22 3 20 3 17V7C3 4 4.5 2 8 2H16C19.5 2 21 4 21 7Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M14.5 4.5V6.5C14.5 7.6 15.4 8.5 16.5 8.5H18.5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M8 13H12" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M8 17H16" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>Grading Sheet</span>
        </a>

        {{-- Faculty Evaluation --}}
        <a href="{{ route('faculty.evaluation') }}" class="sidebar-link {{ request()->routeIs('faculty.evaluation') ? 'active' : '' }}">
            {{-- Vuesax linear/star --}}
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M13.73 3.51L15.49 7.03C15.73 7.52 16.37 7.99 16.91 8.08L20.1 8.61C22.14 8.95 22.62 10.43 21.15 11.89L18.67 14.37C18.25 14.79 18.02 15.6 18.15 16.18L18.86 19.25C19.42 21.68 18.13 22.62 15.98 21.35L12.99 19.58C12.45 19.26 11.56 19.26 11.01 19.58L8.02 21.35C5.88 22.62 4.58 21.67 5.14 19.25L5.85 16.18C5.98 15.6 5.75 14.79 5.33 14.37L2.85 11.89C1.39 10.43 1.86 8.95 3.9 8.61L7.09 8.08C7.62 7.99 8.26 7.52 8.5 7.03L10.26 3.51C11.22 1.6 12.78 1.6 13.73 3.51Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>Faculty Evaluation</span>
        </a>

    </nav>

    {{-- Log Out --}}
    <div class="sidebar-logout">
        <a href="{{ route('logout') }}" class="sidebar-link logout-link"
           onclick="event.preventDefault(); document.getElementById('faculty-logout-form').submit();">
            <span>Log Out</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H4.5z"/>
            </svg>
        </a>
        <form id="faculty-logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>
</aside>
