<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'PLP - Student Portal')</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom App CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ time() }}">

    @stack('styles')
</head>
<body class="student-body student-portal-body">
    <div class="student-layout">
        {{-- Mobile overlay --}}
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        {{-- Sidebar --}}
        @include('includes.sidebar')

        {{-- Main wrapper (header + content + footer) --}}
        <div class="student-main-wrapper">
            {{-- Top Header Bar --}}
            <header class="student-topbar">
                {{-- Hamburger toggle (visible on mobile) --}}
                <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <line x1="3" y1="12" x2="21" y2="12"/>
                        <line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>

                <a href="{{ route('student.profile') }}" class="topbar-user">
                    <img src="{{ asset('img/profile.png') }}" alt="User Avatar" class="topbar-avatar">
                </a>
            </header>

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

    <!-- Download Success Toast Notification -->
    <div id="download-toast" class="toast-notification">
        <span class="toast-message">File Downloaded Successfully.</span>
        <button class="toast-close">&times;</button>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script src="{{ asset('js/script.js') }}"></script>

    @stack('scripts')

    {{-- Sidebar toggle for mobile --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.querySelector('.plp-sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const toggleBtn = document.getElementById('sidebarToggle');

            if (toggleBtn && sidebar && overlay) {
                toggleBtn.addEventListener('click', function () {
                    sidebar.classList.toggle('sidebar-open');
                    overlay.classList.toggle('active');
                });

                overlay.addEventListener('click', function () {
                    sidebar.classList.remove('sidebar-open');
                    overlay.classList.remove('active');
                });
            }
        });
    </script>
</body>
</html>
