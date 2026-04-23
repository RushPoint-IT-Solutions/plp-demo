<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'PLP - Faculty Portal')</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('img/logobg.png') }}" type="image/png">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom App CSS -->
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <link rel="stylesheet" href="{{ mix('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/faculty-notifications.css') }}">

    @stack('styles')
</head>
<body class="student-body student-portal-body faculty-body @yield('body-class')">
    @php
        $facultyNotifications = isset($facultyNotifications) ? $facultyNotifications : collect();
        $facultyUnreadNotificationCount = isset($facultyUnreadNotificationCount) ? (int) $facultyUnreadNotificationCount : 0;
    @endphp
    <div class="student-layout">
        {{-- Mobile overlay --}}
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        {{-- Faculty Sidebar --}}
        @include('includes.faculty-sidebar')

        {{-- Main wrapper (header + content + footer) --}}
        <div class="student-main-wrapper">
            {{-- Top Header Bar with icons --}}
            <header class="student-topbar">
                {{-- Hamburger toggle (visible on mobile) --}}
                <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <line x1="3" y1="12" x2="21" y2="12"/>
                        <line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>

                <div class="topbar-icons">
                    {{-- Notification Bell --}}
                    @include('includes.portal-notifications-dropdown', [
                        'notificationContainerId' => 'facultyNotificationsDropdown',
                        'notificationTitle' => 'NOTIFICATIONS',
                        'notificationDetailModalId' => 'facultyNotificationDetailModal',
                        'notificationDetailTitleId' => 'facultyNotificationDetailTitle',
                        'notificationDetailMessageId' => 'facultyNotificationDetailMessage',
                        'notifications' => $facultyNotifications,
                        'unreadCount' => $facultyUnreadNotificationCount,
                        'feedUrl' => route('faculty.notifications.feed'),
                        'markReadUrl' => route('faculty.notifications.mark-read'),
                    ])

                    {{-- Messages --}}
                    <a href="{{ route('faculty.messaging') }}" class="topbar-icon-link msg-icon {{ request()->routeIs('faculty.messaging') ? 'is-active' : '' }}" title="Messages">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                    </a>

                    {{-- Profile Avatar --}}
                    @php
                        $facultyPhoto = null;
                        $facultyUser = auth()->user();
                        if ($facultyUser && !empty($facultyUser->faculty_id) && \Illuminate\Support\Facades\Schema::hasTable('master_faculty_files')) {
                            $facultyRow = \App\MasterFacultyFile::where('code', $facultyUser->username)->first();
                            if (!$facultyRow && !empty($facultyUser->name)) {
                                $facultyRow = \App\MasterFacultyFile::where('name', $facultyUser->name)->first();
                            }
                            if ($facultyRow && is_array($facultyRow->config_payload)) {
                                $state = isset($facultyRow->config_payload['form_state']) && is_array($facultyRow->config_payload['form_state'])
                                    ? $facultyRow->config_payload['form_state']
                                    : [];
                                if (!empty($state['profile_photo_path'])) {
                                    $facultyPhoto = (string) $state['profile_photo_path'];
                                }
                            }
                        }
                    @endphp
                    <a href="{{ route('faculty.profile') }}" class="topbar-user topbar-profile-trigger {{ request()->routeIs('faculty.profile') || request()->routeIs('faculty.profile.edit') ? 'is-active' : '' }}" title="Profile">
                        @if($facultyPhoto)
                            <img src="{{ asset('storage/' . $facultyPhoto) }}" alt="User Avatar" class="topbar-avatar">
                        @else
                            <div class="topbar-avatar-placeholder topbar-avatar-placeholder--neutral">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                            </div>
                        @endif
                    </a>
                </div>
            </header>

            <div class="content-footer-wrap">
                {{-- Green Title Bar --}}
                <div class="student-page-header">
                    @yield('page-title', 'Faculty Load')
                </div>

                {{-- Page Content --}}
                <main class="student-content">
                    @yield('content')
                </main>

                {{-- Footer --}}
                @include('includes.footer')
            </div>
        </div>
    </div>

    <div id="download-toast" class="toast-notification">
        <span class="toast-message">Saved successfully.</span>
        <button class="toast-close">&times;</button>
    </div>

    {{-- Notifications JS --}}
    <script src="{{ asset('js/portal-notifications.js') }}"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script src="{{ asset('js/script.js') }}"></script>

    <!-- Faculty Sidebar JS -->
    <script src="{{ asset('js/faculty-layout.js') }}"></script>

    @stack('scripts')
</body>
</html>
