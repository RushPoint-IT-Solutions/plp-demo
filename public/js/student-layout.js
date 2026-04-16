document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.querySelector('.plp-sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggleBtn = document.getElementById('sidebarToggle');

    if (toggleBtn && sidebar && overlay) {
        toggleBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            sidebar.classList.toggle('sidebar-open');
            overlay.classList.toggle('active');
        });

        overlay.addEventListener('click', function () {
            sidebar.classList.remove('sidebar-open');
            overlay.classList.remove('active');
        });

        // Close on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                sidebar.classList.remove('sidebar-open');
                overlay.classList.remove('active');
            }
        });
    }
});
