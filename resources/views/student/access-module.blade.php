<!-- resources/views/student/access-module.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Module - PLP</title>
    @include('includes.style')
</head>
<body>

    <!-- Header -->
    @include('includes.header')

    <!-- Main Content -->
    <main class="access-module-section">
        
        <!-- Background Watermark -->
        <div class="bg-watermark">
            <img src="{{ asset('resources/images/watermark.png') }}" alt="Watermark">
        </div>

        <!-- Title -->
        <h1 class="access-module-title">Access Module</h1>

        <!-- Cards -->
        <div class="module-cards-wrapper">
            
            <!-- Applicant Card -->
            <a href="/applicant" class="module-card">
                <div class="module-card-icon"></div>
                <div class="module-card-btn">Applicant</div>
            </a>

            <!-- Student Card -->
            <a href="/student" class="module-card">
                <div class="module-card-icon"></div>
                <div class="module-card-btn">Student</div>
            </a>

        </div>
    </main>

    <!-- Footer -->
    @include('includes.footer')

</body>
</html>