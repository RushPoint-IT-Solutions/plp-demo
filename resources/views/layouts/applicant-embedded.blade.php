<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'PLP - Application Form')</title>

    <link rel="icon" href="{{ asset('img/logobg.png') }}" type="image/png">
    @include('includes.pwa-meta')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <link rel="stylesheet" href="{{ mix('css/style.css') }}">

    <style>
        html,
        body {
            background: #f5f7f9;
            margin: 0;
            padding: 0;
        }

        .student-content {
            padding: 0;
            margin: 0;
            background: transparent;
        }

        .profile-page.application-form-page {
            margin-top: 0;
        }
    </style>

    @stack('styles')
</head>
<body class="student-body student-portal-body applicant-body applicant-embedded-mode">
    <main class="student-content">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/script.js') }}"></script>
    <script src="{{ mix('js/applicant-select.js') }}"></script>

    @include('includes.pwa-script')
    @stack('scripts')
</body>
</html>
