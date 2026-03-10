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
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ time() }}">

    @stack('styles')
</head>
<body class="student-body student-portal-body registrar-body">
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
                    {{-- Notification Bell --}}
                    <a href="#" class="topbar-icon-link" title="Notifications">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                    </a>

                    {{-- Messages --}}
                    <a href="#" class="topbar-icon-link" title="Messages">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                    </a>

                    {{-- Profile Avatar --}}
                    <a href="#" class="topbar-user">
                        <div class="topbar-avatar-placeholder" style="width:36px;height:36px;border-radius:50%;background:#ccc;display:flex;align-items:center;justify-content:center;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                    </a>
                </div>
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

    <!-- Registrar Toast Notification -->
    <div id="registrar-toast" class="toast-notification">
        <span class="toast-message"></span>
        <button class="toast-close" onclick="document.getElementById('registrar-toast').classList.remove('show')">&times;</button>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script src="{{ asset('js/script.js') }}"></script>

    <!-- Sidebar JS -->
    <script src="{{ asset('js/registrar-layout.js') }}"></script>

    <script>
    function showRegistrarToast(message, type) {
        var toast = document.getElementById('registrar-toast');
        if (!toast) return;
        var messageEl = toast.querySelector('.toast-message');
        if (messageEl) messageEl.textContent = message;
        toast.classList.remove('show', 'toast-error', 'toast-warning');
        if (type === 'warning') toast.classList.add('toast-warning');
        if (type === 'error') toast.classList.add('toast-error');
        void toast.offsetWidth;
        toast.classList.add('show');
        if (toast._timer) clearTimeout(toast._timer);
        toast._timer = setTimeout(function () { toast.classList.remove('show'); }, 3000);
    }
    </script>

    @stack('scripts')
</body>
</html>
