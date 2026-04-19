<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'PLP - Registrar Portal')</title>

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
<body class="student-body student-portal-body registrar-body @yield('body-class')">
    @php
        $registrarNotifications = isset($registrarNotifications) ? $registrarNotifications : collect();
        $registrarUnreadNotificationCount = isset($registrarUnreadNotificationCount) ? (int) $registrarUnreadNotificationCount : 0;
    @endphp
    <div class="student-layout">
        {{-- Mobile overlay --}}
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        {{-- Registrar Sidebar --}}
        @include('includes.registrar-sidebar')

        {{-- Extra sidebars (e.g. applicant detail sidebar) --}}
        @stack('extra-sidebar')

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
                    {{-- Help Center --}}
                    <a href="{{ route('registrar.help.center') }}" class="topbar-icon-link topbar-help-icon {{ request()->routeIs('registrar.help.*') ? 'is-active' : '' }}" title="Help Center">
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

                    {{-- Notification Bell --}}
                    <a href="#" class="topbar-icon-link topbar-notif-icon" title="Notifications" data-bs-toggle="modal" data-bs-target="#registrarNotificationsModal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                        <span class="faculty-notif-badge {{ $registrarUnreadNotificationCount ? '' : 'd-none' }}">{{ $registrarUnreadNotificationCount > 99 ? '99+' : $registrarUnreadNotificationCount }}</span>
                    </a>

                    {{-- Messages --}}
                    <a href="{{ route('registrar.messaging') }}" class="topbar-icon-link msg-icon {{ request()->routeIs('registrar.messaging') ? 'is-active' : '' }}" title="Messages">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                    </a>

                    {{-- Profile Avatar --}}
                    <a href="#" class="topbar-user topbar-profile-trigger">
                        <div class="topbar-avatar-placeholder topbar-avatar-placeholder--neutral">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                    </a>
                </div>
            </header>

            <div class="content-footer-wrap">
                {{-- Green Title Bar --}}
                <div class="student-page-header">
                    @yield('page-title', 'Dashboard')
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

    <!-- Registrar Toast Notification -->
    <div id="registrar-toast" class="toast-notification">
        <span class="toast-message"></span>
        <button type="button" class="toast-close" id="registrar-toast-close">&times;</button>
    </div>

    <div
        class="modal fade faculty-notif-modal"
        id="registrarNotificationsModal"
        tabindex="-1"
        aria-labelledby="registrarNotificationsTitle"
        aria-hidden="true"
        data-feed-url="{{ route('registrar.notifications.feed') }}"
        data-mark-read-url="{{ route('registrar.notifications.mark-read') }}"
    >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="faculty-notif-modal-header">
                    <h5 class="modal-title" id="registrarNotificationsTitle">NOTIFICATIONS</h5>
                </div>
                <div class="faculty-notif-body">
                    <div class="faculty-notif-list">
                        @forelse($registrarNotifications as $delivery)
                            @php
                                $notification = $delivery->notification;
                                $notificationUrl = $notification ? (string) $notification->local_source_url : '';
                                $isAnnouncementNotification = $notification && (string) $notification->source_module === 'system_announcement';
                                $notificationMessage = $notification ? (string) $notification->message : '';
                            @endphp
                            <div class="faculty-notif-item {{ !empty($delivery->read_at) ? 'is-read' : '' }}" data-delivery-id="{{ $delivery->id }}">
                                @if($isAnnouncementNotification)
                                    <button
                                        type="button"
                                        class="faculty-notif-text faculty-notif-open js-faculty-notif-open"
                                        data-title="{{ $notification ? $notification->title : 'Announcement' }}"
                                        data-message="{{ $notificationMessage }}"
                                    >
                                        {{ $notification ? $notification->title : 'Announcement' }}
                                    </button>
                                @elseif($notification && $notificationUrl)
                                    <a href="{{ $notificationUrl }}" class="faculty-notif-text">{{ $notification->title }}</a>
                                @else
                                    <span class="faculty-notif-text">{{ $notification ? $notification->title : 'New notification' }}</span>
                                @endif
                                <button
                                    type="button"
                                    class="faculty-notif-dismiss js-faculty-notif-dismiss"
                                    data-dismiss-url="{{ route('registrar.notifications.dismiss', ['notificationDelivery' => $delivery->id]) }}"
                                    aria-label="Dismiss notification"
                                    title="Dismiss"
                                >
                                    &times;
                                </button>
                            </div>
                        @empty
                        @endforelse
                    </div>

                    <p class="faculty-notif-empty {{ $registrarNotifications->count() ? 'd-none' : '' }}">No new notifications.</p>
                </div>
            </div>
        </div>
    </div>

    <div
        class="modal fade faculty-notif-detail-modal"
        id="facultyNotificationDetailModal"
        tabindex="-1"
        aria-labelledby="facultyNotificationDetailTitle"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="faculty-notif-modal-header">
                    <h5 class="modal-title" id="facultyNotificationDetailTitle">Announcement</h5>
                </div>
                <div class="faculty-notif-body">
                    <p class="faculty-notif-detail-message" id="facultyNotificationDetailMessage">No details available.</p>
                </div>
                <div class="faculty-notif-detail-footer">
                    <button type="button" class="faculty-notif-detail-close" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script src="{{ asset('js/script.js') }}?v={{ file_exists(public_path('js/script.js')) ? filemtime(public_path('js/script.js')) : time() }}"></script>
    <script src="{{ asset('js/registrar-table-pagination.js') }}?v={{ file_exists(public_path('js/registrar-table-pagination.js')) ? filemtime(public_path('js/registrar-table-pagination.js')) : time() }}"></script>

    <!-- Sidebar JS -->
    <script src="{{ asset('js/registrar-layout.js') }}?v={{ file_exists(public_path('js/registrar-layout.js')) ? filemtime(public_path('js/registrar-layout.js')) : time() }}"></script>

    @stack('scripts')
</body>
</html>
