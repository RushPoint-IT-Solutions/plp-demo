document.querySelectorAll('.sidebar-dropdown-toggle').forEach(function(toggle) {
    toggle.addEventListener('click', function(e) {
        e.preventDefault();
        var dropdown = this.closest('.sidebar-dropdown');
        var menu = dropdown.querySelector('.sidebar-dropdown-menu');
        // Only toggle if the dropdown actually has sub-links or nested dropdowns
        if (menu && (menu.querySelector('.sidebar-sublink') || menu.querySelector('.sidebar-nested-dropdown'))) {
            dropdown.classList.toggle('open');
        }
    });
});

// Nested sub-dropdown toggle (e.g. Academic Master inside Registrar)
document.querySelectorAll('.sidebar-nested-toggle').forEach(function(toggle) {
    toggle.addEventListener('click', function(e) {
        e.preventDefault();
        var nested = this.closest('.sidebar-nested-dropdown');
        nested.classList.toggle('open');
    });
});

// Sidebar toggle (mobile drawer)
var sidebarToggle = document.getElementById('sidebarToggle');
var sidebar = document.querySelector('.plp-sidebar');
var overlay = document.getElementById('sidebarOverlay');

if (sidebarToggle) {
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('sidebar-open');
        overlay.classList.toggle('active');
    });
}

if (overlay) {
    overlay.addEventListener('click', function() {
        sidebar.classList.remove('sidebar-open');
        overlay.classList.remove('active');
    });
}

// Close any open sidebar dropdowns when clicking outside the sidebar
document.addEventListener('click', function(e) {
    // if click is outside the sidebar, close open dropdowns
    if (!e.target.closest('.plp-sidebar')) {
        document.querySelectorAll('.sidebar-dropdown.open, .sidebar-nested-dropdown.open').forEach(function(el) {
            el.classList.remove('open');
        });
    }
});
