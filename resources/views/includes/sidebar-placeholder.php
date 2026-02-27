<!-- resources/views/includes/sidebar-placeholder.blade.php -->
<aside class="plp-sidebar">
    
    <!-- Logo -->
    <div class="sidebar-logo">
        <img src="{{ asset('resources/images/header-logo.png') }}" alt="PLP Logo">
    </div>

    <!-- Menu -->
    <ul class="sidebar-menu">
        <li>
            <a href="/section-offering" class="{{ request()->is('section-offering') ? 'active' : 'sidebar-inactive' }}">
                <i class="bi bi-house-door-fill"></i>
                <span>Section Offering</span>
            </a>
        </li>
        <li>
            <a href="#" class="sidebar-inactive">
                <i class="bi bi-mortarboard"></i>
                <span>Grades</span>
            </a>
        </li>
        <li>
            <a href="/schedule" class="{{ request()->is('schedule') ? 'active' : 'sidebar-inactive' }}">
                <i class="bi bi-calendar-event"></i>
                <span>Schedule</span>
            </a>
        </li>
        <li>
            <a href="#" class="sidebar-inactive">
                <i class="bi bi-ticket-perforated"></i>
                <span>Events</span>
            </a>
        </li>
    </ul>

    <!-- Logout -->
    <a href="/" class="sidebar-menu-link sidebar-logout">
        <span>Log Out</span>
        <i class="bi bi-box-arrow-right"></i>
    </a>
</aside>