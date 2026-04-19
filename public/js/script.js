// ========================================================
//   PLP Web System – Custom JavaScript
// ========================================================

window.PLPComponents = window.PLPComponents || {};

window.PLPComponents.initSelectStyles = function (root) {
    var scope = root && root.querySelectorAll ? root : document;
    scope.querySelectorAll('select[data-plp-select]').forEach(function (select) {
        select.classList.add('plp-select');
    });
};

window.PLPComponents.initServerPagination = function (config) {
    if (!config || !config.root || !config.form || !config.pageInput) {
        return null;
    }

    var root = config.root;
    var form = config.form;
    var pageInput = config.pageInput;
    var currentPage = parseInt(config.currentPage || '1', 10) || 1;
    var lastPage = Math.max(parseInt(config.lastPage || '1', 10) || 1, 1);
    var pageSelector = config.pageSelector || '[data-page]';
    var pageAttribute = config.pageAttribute || 'data-page';
    var resetToFirstOnSubmit = config.resetToFirstOnSubmit !== false;
    var submittingPageNavigation = false;

    form.addEventListener('submit', function () {
        if (resetToFirstOnSubmit && !submittingPageNavigation) {
            pageInput.value = '1';
        }

        submittingPageNavigation = false;
    });

    function submitPage(pageNumber) {
        var targetPage = Number(pageNumber || 1);
        if (!Number.isFinite(targetPage)) {
            return;
        }

        if (targetPage < 1) {
            targetPage = 1;
        }

        if (targetPage > lastPage) {
            targetPage = lastPage;
        }

        if (targetPage === currentPage) {
            return;
        }

        submittingPageNavigation = true;
        pageInput.value = String(targetPage);
        form.submit();
    }

    root.addEventListener('click', function (event) {
        var pageButton = event.target.closest(pageSelector);
        if (!pageButton) {
            return;
        }

        event.preventDefault();
        if (pageButton.disabled || pageButton.getAttribute('aria-disabled') === 'true') {
            return;
        }

        submitPage(pageButton.getAttribute(pageAttribute));
    });

    return {
        submitPage: submitPage,
    };
};

// Wait for DOM to load
document.addEventListener('DOMContentLoaded', function() {
    
    // ===== COR ZOOM: fit full A4 width into available container =====
    //
    // 210mm at 96 dpi = 793.7px. We measure the scroll wrapper's actual
    // pixel width and set zoom so the COR fills it exactly (max 1.0).
    // Called on show + on every window resize.

    const COR_NATIVE_WIDTH = 794; // 210mm @ 96dpi

    function fitCorToContainer() {
        const wrapper = document.querySelector('.cor-scroll-wrapper');
        if (!wrapper || !corTable) return;
        const available = wrapper.clientWidth;
        // Allow zoom up to 1.25 so the COR fills more space on wide screens
        const zoom = Math.min(1.25, available / COR_NATIVE_WIDTH);
        corTable.style.zoom = zoom.toFixed(4);
    }

    // Re-fit on viewport resize
    window.addEventListener('resize', function () {
        if (corTable && corTable.style.display !== 'none') {
            fitCorToContainer();
        }
    });

    // ===== VIEW/DOWNLOAD COR FUNCTIONALITY =====
    //
    // STATE FLOW:
    //   State 0 (default)  – filter visible | COR hidden  | btn = "View COR"
    //   State 1 (view)     – filter visible | COR visible | btn = "Download COR"
    //   State 2 (download) – filter hidden  | COR visible | toast shown
    //   Click X on toast   – back to State 0

    const corBtn        = document.getElementById('cor-action-btn');   // the action button
    const corTable      = document.getElementById('cor-table');         // the COR table
    const filterSection = document.getElementById('filter-section');    // semester/course/block rows
    const toast         = document.getElementById('download-toast');    // top-right toast
    const toastClose    = document.querySelector('.toast-close');       // × button on toast

    const COR_PRINT_BLOCK_LABEL = 'STUDENT: SCHEDULE (VIEW COR)';
    const COR_PRINT_FALLBACK_TITLE = 'Certificate of Registration - PLP';
    const corPrintSuppressionState = {
        previousTitle: '',
        hiddenNodes: []
    };

    function normalizeCorPrintText(value) {
        return String(value || '')
            .replace(/\s+/g, ' ')
            .trim()
            .toUpperCase();
    }

    function suppressCorChromePrintLabel() {
        if (!corTable || corTable.style.display === 'none') {
            return;
        }

        if (!document.body || !document.body.classList.contains('student-portal-body')) {
            return;
        }

        if (corPrintSuppressionState.previousTitle === '') {
            corPrintSuppressionState.previousTitle = document.title;
        }

        if (normalizeCorPrintText(document.title) === normalizeCorPrintText(COR_PRINT_BLOCK_LABEL)) {
            document.title = COR_PRINT_FALLBACK_TITLE;
        }

        document.querySelectorAll('body *').forEach(function (node) {
            if (!node || node.children.length > 0) {
                return;
            }

            if (normalizeCorPrintText(node.textContent) !== COR_PRINT_BLOCK_LABEL) {
                return;
            }

            if (node.getAttribute('data-cor-print-hidden') === '1') {
                return;
            }

            node.setAttribute('data-cor-print-hidden', '1');
            node.setAttribute('data-cor-print-prev-display', node.style.display || '');
            node.style.display = 'none';
            corPrintSuppressionState.hiddenNodes.push(node);
        });
    }

    function restoreCorChromePrintLabel() {
        while (corPrintSuppressionState.hiddenNodes.length > 0) {
            var node = corPrintSuppressionState.hiddenNodes.pop();
            if (!node) {
                continue;
            }

            var previousDisplay = node.getAttribute('data-cor-print-prev-display');
            if (previousDisplay === null || previousDisplay === '') {
                node.style.removeProperty('display');
            } else {
                node.style.display = previousDisplay;
            }

            node.removeAttribute('data-cor-print-prev-display');
            node.removeAttribute('data-cor-print-hidden');
        }

        if (corPrintSuppressionState.previousTitle !== '') {
            document.title = corPrintSuppressionState.previousTitle;
            corPrintSuppressionState.previousTitle = '';
        }
    }

    if (corBtn && corTable && filterSection) {

        corBtn.addEventListener('click', function () {

            if (this.textContent.trim() === 'View COR') {
                // ── State 0 → 1 ──────────────────────────────────
                // Show the COR table and switch button to "Download COR"
                corTable.style.display = 'block';
                fitCorToContainer();
                this.textContent = 'Download COR';

                // Scroll smoothly so the user sees the table
                corTable.scrollIntoView({ behavior: 'smooth', block: 'start' });

            } else if (this.textContent.trim() === 'Download COR') {
                // ── State 1 → 2 ──────────────────────────────────
                // Hide the filter section, trigger print view, and reset
                filterSection.style.display = 'none';
                suppressCorChromePrintLabel();
                window.print();
                setTimeout(restoreCorChromePrintLabel, 800);
                resetToDefault();
            }
        });
    }

    // ── X button on toast → reset to State 0 ─────────────────────
    if (toastClose && toast) {
        toastClose.addEventListener('click', function () {
            hideToast();
            resetToDefault();
        });
    }

    // ===== HELPER FUNCTIONS =====

    /** Show the top-right toast by adding the CSS .show class */
    function showToast() {
        if (toast) {
            toast.classList.add('show');
        }
    }

    /** Hide the toast with a brief fade-out, then remove .show */
    function hideToast() {
        if (toast) {
            toast.style.opacity = '0';
            setTimeout(() => {
                toast.classList.remove('show');
                toast.style.opacity = ''; // let CSS handle it next time
            }, 300);
        }
    }

    /** Reset every element back to the default (State 0) */
    function resetToDefault() {
        if (filterSection) filterSection.style.display = '';
        if (corTable) {
            corTable.style.display = 'none';
            corTable.style.zoom = '';   // clear dynamic zoom
        }
        if (corBtn)  corBtn.textContent = 'View COR';
    }
    
    // ===== PRINT: reset zoom to 1 before printing, restore after =====
    window.addEventListener('beforeprint', function () {
        if (corTable) corTable.style.zoom = '1';
        suppressCorChromePrintLabel();
    });
    window.addEventListener('afterprint', function () {
        restoreCorChromePrintLabel();
        if (corTable && corTable.style.display !== 'none') fitCorToContainer();
    });

    // ===== PASSWORD TOGGLE (for future login pages) =====
    const passwordToggle = document.querySelector('.password-toggle-btn');
    const passwordInput = document.querySelector('.password-input');
    
    if (passwordToggle && passwordInput) {
        passwordToggle.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
        });
    }

    // ===== PROGRAM FILE PAGE =====
    const programFilePage = document.getElementById('programFilePage');
    if (programFilePage) {
        if (window.PLPComponents && typeof window.PLPComponents.initSelectStyles === 'function') {
            window.PLPComponents.initSelectStyles(programFilePage);
        }

        const successMessage = programFilePage.getAttribute('data-success');
        if (successMessage && typeof showRegistrarToast === 'function') {
            showRegistrarToast(successMessage, 'success');
        }

        const shouldOpenSetup = programFilePage.getAttribute('data-open-setup') === '1';
        if (shouldOpenSetup) {
            openSetupDepartmentsModal();
        }

        const shouldOpenNew = programFilePage.getAttribute('data-open-new') === '1';
        if (shouldOpenNew) {
            openNewProgramModal();
        }

        const topFilterForm = document.getElementById('pfTopFilterForm');
        const pageInput = document.getElementById('pfPageInput');
        const currentPage = parseInt(programFilePage.getAttribute('data-current-page') || '1', 10) || 1;
        const lastPage = parseInt(programFilePage.getAttribute('data-last-page') || '1', 10) || 1;

        if (window.PLPComponents && typeof window.PLPComponents.initServerPagination === 'function' && topFilterForm && pageInput) {
            window.PLPComponents.initServerPagination({
                root: programFilePage,
                form: topFilterForm,
                pageInput: pageInput,
                currentPage: currentPage,
                lastPage: lastPage,
                pageSelector: '[data-pf-page]',
                pageAttribute: 'data-pf-page',
                resetToFirstOnSubmit: true,
            });
        }

        const editModal = document.getElementById('pfEditProgramModal');
        const deleteModal = document.getElementById('pfDeleteProgramModal');
        const editForm = document.getElementById('pfEditProgramForm');
        const deleteForm = document.getElementById('pfDeleteProgramForm');
        const deleteText = document.getElementById('pfDeleteProgramText');

        function closeProgramMenus() {
            programFilePage.querySelectorAll('.apst-dropdown.open').forEach(function (menu) {
                menu.classList.remove('open');
            });
        }

        function positionProgramMenu(menu, trigger) {
            if (!menu || !trigger) {
                return;
            }

            const rect = trigger.getBoundingClientRect();
            menu.style.position = 'fixed';
            menu.style.left = (rect.right - 170) + 'px';
            menu.style.top = (rect.bottom + 6) + 'px';
            menu.style.zIndex = '2000';

            const menuRect = menu.getBoundingClientRect();
            if (menuRect.right > window.innerWidth - 8) {
                menu.style.left = Math.max(8, window.innerWidth - menuRect.width - 8) + 'px';
            }
            if (menuRect.bottom > window.innerHeight - 8) {
                menu.style.top = Math.max(8, rect.top - menuRect.height - 6) + 'px';
            }
        }

        function openPfEditModal(trigger) {
            if (!editModal || !editForm) {
                return;
            }

            editForm.action = trigger.getAttribute('data-update-url') || '';
            const codeInput = document.getElementById('pfEditProgramCode');
            const nameInput = document.getElementById('pfEditProgramName');
            const departmentInput = document.getElementById('pfEditDepartment');
            const accreditationInput = document.getElementById('pfEditAccreditation');

            if (codeInput) {
                codeInput.value = trigger.getAttribute('data-code') || '';
            }
            if (nameInput) {
                nameInput.value = trigger.getAttribute('data-name') || '';
            }
            if (departmentInput) {
                departmentInput.value = trigger.getAttribute('data-department-id') || '';
            }
            if (accreditationInput) {
                accreditationInput.value = trigger.getAttribute('data-accreditation') || 'Pending Review';
            }

            editModal.style.display = 'flex';
        }

        function openPfDeleteModal(trigger) {
            if (!deleteModal || !deleteForm) {
                return;
            }

            deleteForm.action = trigger.getAttribute('data-delete-url') || '';
            if (deleteText) {
                deleteText.textContent = 'Are you sure you want to delete program "' + (trigger.getAttribute('data-code') || '') + '"?';
            }
            deleteModal.style.display = 'flex';
        }

        programFilePage.addEventListener('click', function (event) {
            const toggleBtn = event.target.closest('[data-pf-menu-toggle]');
            if (toggleBtn) {
                event.preventDefault();
                const menu = document.getElementById(toggleBtn.getAttribute('data-pf-menu-toggle'));
                if (!menu) {
                    return;
                }

                const willOpen = !menu.classList.contains('open');
                closeProgramMenus();

                if (willOpen) {
                    menu.classList.add('open');
                    positionProgramMenu(menu, toggleBtn);
                }
                return;
            }

            const actionBtn = event.target.closest('[data-pf-action]');
            if (actionBtn) {
                event.preventDefault();
                const menu = actionBtn.closest('.apst-dropdown');
                const row = menu ? menu.closest('tr') : null;
                const trigger = row ? row.querySelector('[data-pf-menu-toggle]') : null;
                const action = actionBtn.getAttribute('data-pf-action');

                closeProgramMenus();

                if (!trigger) {
                    return;
                }

                if (action === 'edit') {
                    openPfEditModal(trigger);
                } else if (action === 'delete') {
                    openPfDeleteModal(trigger);
                }
                return;
            }

            if (!event.target.closest('.apst-dropdown')) {
                closeProgramMenus();
            }
        });

        window.addEventListener('resize', function () {
            const openMenu = programFilePage.querySelector('.apst-dropdown.open');
            if (!openMenu) {
                return;
            }

            const row = openMenu.closest('tr');
            const trigger = row ? row.querySelector('[data-pf-menu-toggle]') : null;
            if (trigger) {
                positionProgramMenu(openMenu, trigger);
            }
        });
    }
});

function openSetupDepartmentsModal() {
    const modal = document.getElementById('setupDepartmentsModal');
    if (modal) modal.style.display = 'flex';
}

function openNewProgramModal() {
    const modal = document.getElementById('newProgramModal');
    if (modal) modal.style.display = 'flex';
}

function closeNewProgramModal() {
    const modal = document.getElementById('newProgramModal');
    if (modal) modal.style.display = 'none';
}

function closeSetupDepartmentsModal() {
    const modal = document.getElementById('setupDepartmentsModal');
    if (modal) modal.style.display = 'none';
}

function closePfEditProgramModal() {
    const modal = document.getElementById('pfEditProgramModal');
    if (modal) modal.style.display = 'none';
}

function closePfDeleteProgramModal() {
    const modal = document.getElementById('pfDeleteProgramModal');
    if (modal) modal.style.display = 'none';
}

document.addEventListener('click', function (event) {
    const overlays = document.querySelectorAll('.pf-modal-overlay');
    overlays.forEach(function (overlay) {
        if (event.target === overlay) {
            overlay.style.display = 'none';
        }
    });
});