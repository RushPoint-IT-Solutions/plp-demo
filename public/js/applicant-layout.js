// Applicant portal layout JS – sidebar toggle (mirrors faculty-layout.js)
(function () {
    var toggle  = document.getElementById('sidebarToggle');
    var overlay = document.getElementById('sidebarOverlay');
    var sidebar = document.querySelector('.plp-sidebar');

    if (!toggle || !overlay || !sidebar) return;

    toggle.addEventListener('click', function () {
        sidebar.classList.toggle('sidebar-open');
        overlay.classList.toggle('active');
        overlay.classList.toggle('overlay-visible');
    });

    overlay.addEventListener('click', function () {
        sidebar.classList.remove('sidebar-open');
        overlay.classList.remove('active');
        overlay.classList.remove('overlay-visible');
    });

    var printExamBtn = document.getElementById('applicantPrintExamBtn');
    if (printExamBtn) {
        printExamBtn.addEventListener('click', function () {
            window.print();
        });
    }

    var saveExamBtn = document.getElementById('applicantSaveExamBtn');
    if (saveExamBtn) {
        saveExamBtn.addEventListener('click', function () {
            saveExamBtn.blur();
        });
    }
})();
