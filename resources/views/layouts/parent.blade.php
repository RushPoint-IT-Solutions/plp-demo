<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'PLP - Parent Portal')</title>

    <link rel="icon" href="{{ asset('img/logobg.png') }}" type="image/png">
    @include('includes.pwa-meta')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <link rel="stylesheet" href="{{ mix('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/faculty-notifications.css') }}">

    @stack('styles')
</head>
<body class="student-body student-portal-body parent-portal-body @yield('body-class')">
    @php
        $parentNotifications = $parentNotifications ?? collect();
        $parentUnreadNotificationCount = (int) ($parentUnreadNotificationCount ?? 0);
    @endphp

    <div class="student-layout">
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        @include('includes.parent-sidebar')

        <div class="student-main-wrapper">
            <header class="student-topbar">
                <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <line x1="3" y1="12" x2="21" y2="12"/>
                        <line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>

                <div class="topbar-icons">
                    <a href="{{ route('parent.help.center') }}" class="topbar-icon-link topbar-help-icon {{ request()->routeIs('parent.help.*') ? 'is-active' : '' }}" title="Help Center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 12a8 8 0 0 1 16 0"/>
                            <path d="M4 12v5a2 2 0 0 0 2 2h1"/>
                            <path d="M20 12v5a2 2 0 0 1-2 2h-1"/>
                            <rect x="3" y="11" width="4" height="6" rx="2"/>
                            <rect x="17" y="11" width="4" height="6" rx="2"/>
                            <path d="M12 19v2"/>
                            <path d="M10 21h4"/>
                        </svg>
                    </a>

                    @include('includes.portal-notifications-dropdown', [
                        'notificationContainerId' => 'parentNotificationsDropdown',
                        'notificationTitle' => 'NOTIFICATIONS',
                        'notifications' => $parentNotifications,
                        'unreadCount' => $parentUnreadNotificationCount,
                        'feedUrl' => route('parent.notifications.feed'),
                        'markReadUrl' => route('parent.notifications.mark-read'),
                    ])

                    <a href="{{ route('parent.messaging') }}" class="topbar-icon-link msg-icon {{ request()->routeIs('parent.messaging') ? 'is-active' : '' }}" title="Messages">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                    </a>

                    <a href="{{ route('parent.profile') }}" class="topbar-user topbar-profile-trigger {{ request()->routeIs('parent.profile') ? 'is-active' : '' }}" title="Parent Profile">
                        <div class="topbar-avatar-placeholder topbar-avatar-placeholder--neutral">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                    </a>
                </div>
            </header>

            <div class="content-footer-wrap">
                <div class="student-page-header">
                    @yield('page-title', 'PARENT PORTAL')
                </div>

                <main class="student-content">
                    @yield('content')
                </main>

                @include('includes.footer')
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/script.js') }}"></script>

    @stack('scripts')

    <script src="{{ asset('js/portal-notifications.js') }}"></script>
    <script src="{{ asset('js/student-layout.js') }}"></script>
    <script src="{{ asset('js/student-sidebar-dropdown.js') }}"></script>
    @include('includes.pwa-script')
</body>
</html>
