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
        const zoom = Math.min(1, available / COR_NATIVE_WIDTH);
        corTable.style.zoom = zoom.toFixed(4);
        // Keep the wrapper height in sync so page doesn't leave empty space
        corTable.style.marginTop = '16px';
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
                // Hide the filter section and show the download toast
                filterSection.style.display = 'none';
                showToast();
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
    
});