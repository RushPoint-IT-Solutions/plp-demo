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
