// ========================================================
//   PLP Web System – Custom JavaScript
// ========================================================

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
                window.print();
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
    });
    window.addEventListener('afterprint', function () {
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

        const entriesSelect = document.getElementById('pfEntriesLimit');
        const table = document.getElementById('pfTable');

        if (entriesSelect && table) {
            const tableBody = table.querySelector('tbody');
            const headerColumnsCount = table.querySelectorAll('thead th').length || 1;
            const tableRows = tableBody ? Array.from(tableBody.querySelectorAll('tr')) : [];
            const emptyRows = tableRows.filter(function (row) {
                return row.querySelector('.pf-empty-row') !== null;
            });
            const dataRows = tableRows.filter(function (row) {
                return row.querySelector('.pf-empty-row') === null;
            });

            function applyEntryLimit() {
                const limit = parseInt(entriesSelect.value, 10);
                const total = dataRows.length;
                const visibleCount = Number.isNaN(limit) ? total : Math.min(limit, total);

                dataRows.forEach(function (row, index) {
                    row.style.display = index < visibleCount ? '' : 'none';
                });

                emptyRows.forEach(function (row) {
                    row.style.display = total === 0 ? '' : 'none';
                });

                if (tableBody) {
                    const oldTotalRow = tableBody.querySelector('.pf-total-row');
                    if (oldTotalRow) {
                        oldTotalRow.remove();
                    }

                    const totalRow = document.createElement('tr');
                    totalRow.className = 'pf-total-row';
                    totalRow.innerHTML = '<td colspan="' + headerColumnsCount + '" class="pf-total-cell">Total Programs: <strong>' + visibleCount + '</strong></td>';
                    tableBody.appendChild(totalRow);
                }
            }

            entriesSelect.addEventListener('change', applyEntryLimit);
            applyEntryLimit();
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