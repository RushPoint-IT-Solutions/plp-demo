<!-- resources/views/includes/dashboard-header.blade.php -->
<div class="dashboard-top-bar">
    <!-- Profile Picture -->
    <div class="profile-pic-placeholder" style="width:40px;height:40px;border-radius:50%;background:#ccc;display:flex;align-items:center;justify-content:center;">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
        </svg>
    </div>
</div>

<div class="dashboard-header-divider"></div>

<div class="dashboard-title-bar">
    @yield('page-title', 'Section Offering')
</div>

