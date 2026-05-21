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

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">


    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">

    <!-- Remix Icon -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom App CSS -->
    <link rel="stylesheet" href="{{ mix('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/faculty-notifications.css') }}">

    @stack('styles')
    
    <style>
        .sidebar-logo
        {
            width: 70px !important;
            height: 70px !important;
            object-fit: contain;
        }

        #religionsTable thead tr th {
            background: #2d6a4f !important;
            color: #fff !important;
            font-weight: 600;
            font-size: 0.84rem;
            white-space: nowrap;
            border: none !important;
        }
        #religionsTable thead .sorting:after,
        #religionsTable thead .sorting_asc:after,
        #religionsTable thead .sorting_desc:after,
        #religionsTable thead .sorting:before,
        #religionsTable thead .sorting_asc:before,
        #religionsTable thead .sorting_desc:before {
            color: rgba(255, 255, 255, 0.6) !important;
            opacity: 1 !important;
        }
        #religionsTable tbody tr td {
            border-left: none !important;
            border-right: none !important;
            vertical-align: middle;
        }
        #religionsTable tbody tr:hover td {
            background: #f8fffe;
        }

        .card-title-underline {
            height: 3px;
            background: #2d6a4f;
            border-radius: 2px;
        }

        #table-filter-control .dataTables_filter label {
            display: flex;
            align-items: center;
            gap: 0;
            margin: 0;
        }
        #table-filter-control .dataTables_filter label span { display: none; }
        #table-filter-control .dataTables_filter input {
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 6px 12px 6px 36px;
            font-size: 0.85rem;
            color: #495057;
            background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='%236c757d' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cline x1='21' y1='21' x2='16.65' y2='16.65'/%3E%3C/svg%3E") no-repeat 10px center;
            background-size: 15px;
            width: 220px;
        }
        #table-filter-control .dataTables_filter input:focus {
            outline: none;
            border-color: #2d6a4f;
            box-shadow: none;
        }
        #table-filter-control .dataTables_filter input::placeholder { color: #adb5bd; }

        #table-length-control .dataTables_length label {
            display: flex;
            align-items: center;
            gap: 6px;
            margin: 0;
            font-size: 0.85rem;
            color: #495057;
        }
        #table-length-control .dataTables_length select {
            padding: 5px 28px 5px 8px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            font-size: 0.85rem;
            color: #495057;
        }

        #table-buttons-control .dt-buttons { display: flex; gap: 6px; margin: 0 !important; }
        #table-buttons-control .dt-button,
        #table-buttons-control .dt-button.buttons-copy,
        #table-buttons-control .dt-button.buttons-csv,
        #table-buttons-control .dt-button:focus,
        #table-buttons-control .dt-button:active,
        #table-buttons-control .dt-button.active {
            background: #fff !important;
            background-color: #fff !important;
            background-image: none !important;
            border: 1px solid #dee2e6 !important;
            color: #495057 !important;
            padding: 6px 14px !important;
            border-radius: 6px !important;
            font-size: 0.82rem !important;
            font-weight: 500 !important;
            cursor: pointer !important;
            box-shadow: none !important;
            text-shadow: none !important;
        }
        #table-buttons-control .dt-button:hover,
        #table-buttons-control .dt-button.buttons-copy:hover,
        #table-buttons-control .dt-button.buttons-csv:hover {
            background: #f8f9fa !important;
            background-color: #f8f9fa !important;
            background-image: none !important;
            border-color: #adb5bd !important;
            color: #333 !important;
            box-shadow: none !important;
        }

        #table-info-control .dataTables_info {
            font-size: 0.82rem;
            color: #6c757d;
            margin: 0 !important;
            padding: 0 !important;
        }
        .dataTables_processing {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 200px;
            text-align: center;
            padding: 16px 20px;
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
            z-index: 1000;
            font-size: 0.85rem;
            color: #495057;
        }
        .registrar-topbar-context {
            flex: 1 1 auto;
            min-width: 0;
            padding-left: 12px;
        }
        .registrar-topbar-kicker {
            display: block;
            color: #6b7a70;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0;
            line-height: 1.1;
            text-transform: uppercase;
        }
        .registrar-topbar-title {
            display: block;
            color: #143521;
            font-size: 0.95rem;
            font-weight: 800;
            line-height: 1.2;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .registrar-profile-menu-wrap {
            position: relative;
        }
        .registrar-profile-menu {
            border: 1px solid #e6ece8;
            border-radius: 8px;
            box-shadow: 0 14px 32px rgba(20, 53, 33, 0.14);
            min-width: 220px;
            padding: 8px;
        }
        .registrar-profile-menu-header {
            border-bottom: 1px solid #eef2ef;
            margin-bottom: 6px;
            padding: 8px 10px 10px;
        }
        .registrar-profile-menu-name {
            color: #143521;
            font-size: 0.9rem;
            font-weight: 800;
            line-height: 1.2;
        }
        .registrar-profile-menu-email {
            color: #6b7a70;
            font-size: 0.76rem;
            line-height: 1.25;
            margin-top: 3px;
            word-break: break-word;
        }
        .registrar-profile-menu .dropdown-item {
            align-items: center;
            border-radius: 6px;
            color: #234131;
            display: flex;
            font-size: 0.84rem;
            font-weight: 700;
            gap: 8px;
            padding: 9px 10px;
        }
        .registrar-profile-menu .dropdown-item:hover,
        .registrar-profile-menu .dropdown-item:focus {
            background: #edf7f0;
            color: #0f5f36;
        }
        @media (max-width: 640px) {
            .registrar-topbar-context {
                padding-left: 4px;
            }
            .registrar-topbar-title {
                font-size: 0.82rem;
                max-width: 42vw;
            }
        }
    </style>

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

                <div class="registrar-topbar-context" aria-label="Current page">
                    <span class="registrar-topbar-kicker">Registrar Portal</span>
                    <span class="registrar-topbar-title">@yield('page-title', 'Dashboard')</span>
                </div>

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
                    @include('includes.portal-notifications-dropdown', [
                        'notificationContainerId' => 'registrarNotificationsDropdown',
                        'notificationTitle' => 'NOTIFICATIONS',
                        'notificationDetailModalId' => 'registrarNotificationDetailModal',
                        'notificationDetailTitleId' => 'registrarNotificationDetailTitle',
                        'notificationDetailMessageId' => 'registrarNotificationDetailMessage',
                        'notifications' => $registrarNotifications,
                        'unreadCount' => $registrarUnreadNotificationCount,
                        'feedUrl' => route('registrar.notifications.feed'),
                        'markReadUrl' => route('registrar.notifications.mark-read'),
                    ])

                    {{-- Messages --}}
                    <a href="{{ route('registrar.messaging') }}" class="topbar-icon-link msg-icon {{ request()->routeIs('registrar.messaging') ? 'is-active' : '' }}" title="Messages">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                    </a>

                    {{-- Profile Avatar --}}
                    <div class="dropdown registrar-profile-menu-wrap">
                        <a href="#" class="topbar-user topbar-profile-trigger {{ request()->routeIs('registrar.profile') ? 'is-active' : '' }}" id="registrarProfileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Profile menu">
                            <div class="topbar-avatar-placeholder topbar-avatar-placeholder--neutral">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end registrar-profile-menu" aria-labelledby="registrarProfileDropdown">
                            <div class="registrar-profile-menu-header">
                                <div class="registrar-profile-menu-name">{{ optional(auth()->user())->name ?: 'Registrar User' }}</div>
                                <div class="registrar-profile-menu-email">{{ optional(auth()->user())->email ?: optional(auth()->user())->username }}</div>
                            </div>
                            <a class="dropdown-item" href="{{ route('registrar.profile') }}">
                                <i class="bi bi-person-circle"></i>
                                <span>My Profile</span>
                            </a>
                        </div>
                    </div>
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

    {{-- Notifications JS --}}
    <script src="{{ asset('js/portal-notifications.js') }}"></script>

    <!-- jQuery (MUST be before everything else) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Custom JS -->
    <script src="{{ asset('js/script.js') }}?v={{ file_exists(public_path('js/script.js')) ? filemtime(public_path('js/script.js')) : time() }}"></script>
    <script src="{{ asset('js/registrar-table-pagination.js') }}?v={{ file_exists(public_path('js/registrar-table-pagination.js')) ? filemtime(public_path('js/registrar-table-pagination.js')) : time() }}"></script>

    <!-- Sidebar JS -->
    <script src="{{ asset('js/registrar-layout.js') }}?v={{ file_exists(public_path('js/registrar-layout.js')) ? filemtime(public_path('js/registrar-layout.js')) : time() }}"></script>

    @stack('scripts')
</body>
</html>
