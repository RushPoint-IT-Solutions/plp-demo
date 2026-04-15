(function () {
    function closeSiblingTopDropdowns(currentDropdown) {
        if (!currentDropdown || !currentDropdown.parentElement) {
            return;
        }

        Array.prototype.forEach.call(currentDropdown.parentElement.children, function (dropdown) {
            if (!dropdown.classList || !dropdown.classList.contains('sidebar-dropdown')) {
                return;
            }

            if (dropdown !== currentDropdown) {
                dropdown.classList.remove('open');
            }
        });
    }

    function closeSiblingNestedDropdowns(currentNested) {
        if (!currentNested || !currentNested.parentElement) {
            return;
        }

        Array.prototype.forEach.call(currentNested.parentElement.children, function (nested) {
            if (!nested.classList || !nested.classList.contains('sidebar-nested-dropdown')) {
                return;
            }

            if (nested !== currentNested) {
                nested.classList.remove('open');
            }
        });
    }

    function bindSidebarDropdownToggles() {
        document.querySelectorAll('.sidebar-dropdown-toggle').forEach(function (toggle) {
            toggle.addEventListener('click', function (e) {
                e.preventDefault();
                var dropdown = this.closest('.sidebar-dropdown');
                var menu = dropdown ? dropdown.querySelector('.sidebar-dropdown-menu') : null;
                if (menu && (menu.querySelector('.sidebar-sublink') || menu.querySelector('.sidebar-nested-dropdown'))) {
                    var willOpen = !dropdown.classList.contains('open');
                    closeSiblingTopDropdowns(dropdown);
                    dropdown.classList.toggle('open', willOpen);
                }
            });
        });

        document.querySelectorAll('.sidebar-nested-toggle').forEach(function (toggle) {
            toggle.addEventListener('click', function (e) {
                e.preventDefault();
                var nested = this.closest('.sidebar-nested-dropdown');
                if (nested) {
                    var willOpen = !nested.classList.contains('open');
                    closeSiblingNestedDropdowns(nested);
                    nested.classList.toggle('open', willOpen);
                }
            });
        });
    }

    function openActiveSidebarBranches() {
        document.querySelectorAll('.sidebar-link.active, .sidebar-sublink.active').forEach(function (link) {
            var current = link.closest('.sidebar-dropdown, .sidebar-nested-dropdown');
            while (current) {
                current.classList.add('open');
                current = current.parentElement
                    ? current.parentElement.closest('.sidebar-dropdown, .sidebar-nested-dropdown')
                    : null;
            }
        });
    }

    function bindSidebarDrawer() {
        var sidebarToggle = document.getElementById('sidebarToggle');
        var sidebar = document.querySelector('.plp-sidebar');
        var overlay = document.getElementById('sidebarOverlay');

        if (sidebarToggle && sidebar && overlay) {
            sidebarToggle.addEventListener('click', function () {
                sidebar.classList.toggle('sidebar-open');
                overlay.classList.toggle('active');
            });

            overlay.addEventListener('click', function () {
                sidebar.classList.remove('sidebar-open');
                overlay.classList.remove('active');
            });
        }

        document.addEventListener('click', function (e) {
            if (!sidebar || !overlay) {
                return;
            }

            var clickedInsideSidebar = e.target.closest('.plp-sidebar');
            if (!clickedInsideSidebar && sidebar.classList.contains('sidebar-open')) {
                sidebar.classList.remove('sidebar-open');
                overlay.classList.remove('active');
            }
        });
    }

    function bindCogPrintButton() {
        var cogPrintBtn = document.getElementById('cog-print-btn');
        if (cogPrintBtn) {
            cogPrintBtn.addEventListener('click', function (e) {
                e.preventDefault();
                window.print();
            });
        }
    }

    function bindRegistrarLogout() {
        var logoutLink = document.querySelector('.js-registrar-logout');
        var logoutForm = document.getElementById('registrar-logout-form');
        if (logoutLink && logoutForm) {
            logoutLink.addEventListener('click', function (e) {
                e.preventDefault();
                logoutForm.submit();
            });
        }
    }

    function bindToastClose() {
        var toastCloseBtn = document.getElementById('registrar-toast-close');
        if (toastCloseBtn) {
            toastCloseBtn.addEventListener('click', function () {
                var toast = document.getElementById('registrar-toast');
                if (toast) {
                    toast.classList.remove('show');
                }
            });
        }
    }

    function showRegistrarToast(message, type) {
        var toast = document.getElementById('registrar-toast');
        if (!toast) {
            return;
        }

        var messageEl = toast.querySelector('.toast-message');
        if (messageEl) {
            messageEl.textContent = message;
        }

        toast.classList.remove('show', 'toast-error', 'toast-warning');
        if (type === 'warning') {
            toast.classList.add('toast-warning');
        }
        if (type === 'error') {
            toast.classList.add('toast-error');
        }

        void toast.offsetWidth;
        toast.classList.add('show');
        if (toast._timer) {
            clearTimeout(toast._timer);
        }
        toast._timer = setTimeout(function () {
            toast.classList.remove('show');
        }, 3000);
    }

    bindSidebarDropdownToggles();
    openActiveSidebarBranches();
    bindSidebarDrawer();
    bindCogPrintButton();
    bindRegistrarLogout();
    bindToastClose();

    window.showRegistrarToast = showRegistrarToast;
})();
