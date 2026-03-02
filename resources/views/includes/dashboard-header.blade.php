<!-- resources/views/includes/dashboard-header.blade.php -->
<div class="dashboard-top-bar">
    <!-- Profile Picture -->
    <img src="{{ asset('img/profile.png') }}" alt="Profile" class="profile-pic">
</div>

<div class="dashboard-header-divider"></div>

<div class="dashboard-title-bar">
    @yield('page-title', 'Section Offering')
</div>

