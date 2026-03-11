<!-- resources/views/layouts/dashboard.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'PLP Portal')</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('img/logobg.png') }}" type="image/png">
    
    @include('includes.style')
</head>
<body>

    <div class="dashboard-wrapper">
        
        <!-- Sidebar -->
        @include('includes.sidebar-placeholder')

        <!-- Main Content -->
        <div class="dashboard-main">

            @include('includes.dashboard-header')

            <!-- Page Content -->
            <div class="dashboard-content">
                @yield('content')
            </div>

            <!-- Footer -->
            @include('includes.footer')
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Include Custom Scripts -->
    @include('includes.script')

    <!-- Download Success Toast Notification -->
    <div id="download-toast" class="toast-notification">
        <span class="toast-message">File Downloaded Successfully.</span>
        <button class="toast-close">&times;</button>
    </div>

    
</body>
</html>
