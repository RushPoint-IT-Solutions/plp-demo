(function () {
    var originalSidebar    = document.querySelector('.plp-sidebar:not(#applicantSidebar)');
    var applicantSidebar   = document.getElementById('applicantSidebar');
    var appProcessPage     = document.getElementById('appProcessPage');
    var applicantDetailView = document.getElementById('applicantDetailView');
    var pageHeader         = document.querySelector('.student-page-header');

    // Nav link clicks inside applicant sidebar
    document.querySelectorAll('.applicant-nav-link').forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            switchPanel(this.dataset.panel, this.dataset.title, this);
        });
    });

    // Back to Main button
    document.getElementById('backToMainBtn').addEventListener('click', closeApplicant);

    // Make table rows clickable
    document.querySelectorAll('#appProcessPage .app-table tbody tr').forEach(function (row) {
        row.addEventListener('click', function () {
            openApplicant(this.dataset.id, this.dataset.name);
        });
    });

    function openApplicant(id, name) {
        document.getElementById('detailApplicantId').value = id;
        document.getElementById('detailApplicantName').value = name;

        originalSidebar.style.display = 'none';
        applicantSidebar.style.display = 'flex';

        appProcessPage.style.display = 'none';
        applicantDetailView.style.display = 'block';

        // Default panel: Application Form
        switchPanel(
            'application-form',
            'APPLICATION FORM',
            document.querySelector('.applicant-nav-link[data-panel="application-form"]')
        );
    }

    function closeApplicant() {
        originalSidebar.style.display = '';
        applicantSidebar.style.display = 'none';

        appProcessPage.style.display = '';
        applicantDetailView.style.display = 'none';

        pageHeader.textContent = 'APPLICATION PROCESS';
    }

    function switchPanel(panelId, title, linkEl) {
        document.querySelectorAll('.applicant-panel').forEach(function (p) {
            p.classList.remove('active');
        });
        document.querySelectorAll('.applicant-nav-link').forEach(function (l) {
            l.classList.remove('active');
        });

        var panel = document.getElementById('panel-' + panelId);
        if (panel) panel.classList.add('active');
        if (linkEl) linkEl.classList.add('active');

        pageHeader.textContent = title;
    }
})();
