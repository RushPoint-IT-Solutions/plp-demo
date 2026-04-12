/* Grading Sheet Page */

var GS_SECTIONS = [
    {
        id: 1, section: 'BSIT-4A', courseCode: 'CAP 102', description: 'CAPSTONE PROJECT AND RESEARCH 2',
        faculty: 'DIAZ, JONNEL MARK', midterm: '02/21/2025', final: '02/21/2026',
        approvedBy: 'Admin 1', courseFull: 'Bachelor Of Sience In Information Technology', schedule: 'Room No. : TBA',
        students: [
            { studentNo: '2223A8141', name: 'ABILA, MARK V.', fda: false, na: false, midterm: 85.00, final: 85.00, cRating: 85.30, fRating: 2.25, remarks: 'Passed' },
            { studentNo: '2223A8142', name: 'ACEDO, VINCENT', fda: false, na: false, midterm: 85.00, final: 85.00, cRating: 85.30, fRating: 2.25, remarks: 'Passed' }
        ]
    },
    {
        id: 2, section: 'BSCS- 4A', courseCode: 'CAP 102', description: 'CAPSTONE PROJECT AND RESEARCH 2',
        faculty: 'DIAZ, JONNEL MARK', midterm: '02/21/2026', final: '02/21/2026',
        approvedBy: 'Admin 1', courseFull: 'Bachelor Of Science In Computer Science', schedule: 'Room No. : TBA',
        students: [
            { studentNo: '2223A8137', name: 'BARES, MARK JAY', fda: false, na: false, midterm: 85.00, final: 85.00, cRating: 85.30, fRating: 2.25, remarks: 'Passed' },
            { studentNo: '2223A8139', name: 'DELA CRUZ, JUAN', fda: false, na: false, midterm: 85.00, final: 85.00, cRating: 85.30, fRating: 2.25, remarks: 'Passed' },
            { studentNo: '2223A8139', name: 'DELA CRUZ, JUAN', fda: false, na: false, midterm: 85.00, final: 85.00, cRating: 85.30, fRating: 2.25, remarks: 'Passed' }
        ]
    }
];

var GS_ACTIVE_SECTION_ID = null;
var GS_GRADE_MODAL_STATE = {
    phase: 'midterm',
    componentIndex: 0
};

function findSectionById(id) {
    for (var i = 0; i < GS_SECTIONS.length; i++) {
        if (String(GS_SECTIONS[i].id) === String(id)) {
            return GS_SECTIONS[i];
        }
    }
    return null;
}

function renderSectionList() {
    var tbody = document.getElementById('gsListBody');
    if (!tbody) return;

    var html = '';
    for (var i = 0; i < GS_SECTIONS.length; i++) {
        var s = GS_SECTIONS[i];
        html += '<tr class="gs-section-row" data-section-id="' + s.id + '" style="cursor:pointer;">' +
            '<td>' + (i + 1) + '</td>' +
            '<td class="gs-section-link">' + s.section + '</td>' +
            '<td>' + s.courseCode + '</td>' +
            '<td>' + s.description + '</td>' +
            '<td>' + s.faculty + '</td>' +
            '<td class="gs-date-cell">' + s.midterm + '</td>' +
            '<td class="gs-date-cell">' + s.final + '</td>' +
            '<td>' + s.approvedBy + '</td>' +
        '</tr>';
    }
    tbody.innerHTML = html;
    var pageInfo = document.getElementById('gsListPageInfo');
    if (pageInfo) {
        pageInfo.textContent = 'Showing ' + GS_SECTIONS.length + ' sections';
    }
}

function showDetailView(id) {
    var sec = findSectionById(id);
    if (!sec) return;
    GS_ACTIVE_SECTION_ID = sec.id;

    var bannerSection = document.getElementById('gsBannerSection');
    var bannerCourse = document.getElementById('gsBannerCourse');
    var bannerProf = document.getElementById('gsBannerProf');
    var bannerSched = document.getElementById('gsBannerSched');
    var detailBody = document.getElementById('gsDetailBody');
    var detailPageInfo = document.getElementById('gsDetailPageInfo');
    var listView = document.getElementById('gsListView');
    var detailView = document.getElementById('gsDetailView');

    if (!bannerSection || !bannerCourse || !bannerProf || !bannerSched || !detailBody || !listView || !detailView) {
        return;
    }

    bannerSection.textContent = sec.section;
    bannerCourse.textContent = sec.courseFull;
    var profName = sec.faculty.replace(/\w\S*/g, function(t) { return t.charAt(0).toUpperCase() + t.slice(1).toLowerCase(); });
    bannerProf.textContent = profName;
    bannerSched.textContent = sec.schedule;

    var html = '';
    for (var i = 0; i < sec.students.length; i++) {
        var st = sec.students[i];
        html += '<tr>' +
            '<td class="gs-col-num">' + (i + 1) + '</td>' +
            '<td class="gs-col-studno">' + st.studentNo + '</td>' +
            '<td class="gs-col-name">' + st.name + '</td>' +
            '<td class="gs-col-flag"><input type="checkbox" class="gs-checkbox"' + (st.fda ? ' checked' : '') + '></td>' +
            '<td class="gs-col-flag"><input type="checkbox" class="gs-checkbox"' + (st.na ? ' checked' : '') + '></td>' +
            '<td class="gs-col-grade">' + st.midterm.toFixed(2) + '</td>' +
            '<td class="gs-col-grade">' + st.final.toFixed(2) + '</td>' +
            '<td class="gs-col-grade">' + st.cRating.toFixed(2) + '</td>' +
            '<td class="gs-col-grade">' + st.fRating.toFixed(2) + '</td>' +
            '<td class="gs-col-remarks gs-remarks-passed">' + st.remarks + '</td>' +
        '</tr>';
    }
    detailBody.innerHTML = html;
    if (detailPageInfo) {
        detailPageInfo.textContent = 'Showing ' + sec.students.length + ' students';
    }

    listView.style.display = 'none';
    detailView.style.display = 'block';
}

function showListView() {
    var detailView = document.getElementById('gsDetailView');
    var listView = document.getElementById('gsListView');
    if (!detailView || !listView) return;

    closeGradeModal();

    detailView.style.display = 'none';
    listView.style.display = 'block';
}

function handleViewList() {
    renderSectionList();
    showListView();
}

function getDefaultGradeComponents(phase) {
    if (phase === 'midterm') {
        return [
            { label: 'WRITTEN/SEATWORK', weight: 20 },
            { label: 'PERFORMANCE TASK', weight: 20 },
            { label: 'SUMMATIVE MAJOR EXAM', weight: 60 }
        ];
    }

    return [
        { label: 'WRITTEN/SEATWORK', weight: 20 },
        { label: 'PERFORMANCE TASK', weight: 20 },
        { label: 'SUMMATIVE MAJOR EXAM', weight: 60 }
    ];
}

function buildGradeTabHtml(components, activeIndex) {
    var html = '';
    for (var i = 0; i < components.length; i++) {
        var c = components[i];
        html += '<button type="button" class="gs-grade-tab' + (i === activeIndex ? ' is-active' : '') + '" data-component-index="' + i + '">' +
            c.label + ' (' + c.weight + '%)</button>';
    }
    return html;
}

function buildGradeRowsHtml(students, phase, componentIndex) {
    var html = '';
    for (var i = 0; i < students.length; i++) {
        var st = students[i];
        var base = phase === 'midterm' ? Number(st.midterm) : Number(st.final);
        if (isNaN(base)) {
            base = 0;
        }

        var q1 = Math.max(0, Math.min(20, Math.round((base / 100) * 20)));
        var variance = ((i + componentIndex) % 3) - 1;
        var q2 = Math.max(0, Math.min(20, q1 + variance));
        var pe = ((q1 + q2) / 40) * 100;

        html += '<tr>' +
            '<td>' + (i + 1) + '</td>' +
            '<td>' + st.studentNo + '</td>' +
            '<td>' + st.name + '</td>' +
            '<td><input type="number" class="gs-grade-input" min="0" max="20" step="1" value="' + q1 + '"></td>' +
            '<td><input type="number" class="gs-grade-input" min="0" max="20" step="1" value="' + q2 + '"></td>' +
            '<td><input type="text" class="gs-grade-input gs-grade-input-readonly" value="' + pe.toFixed(2) + '" readonly></td>' +
        '</tr>';
    }

    return html;
}

function renderGradeModalContent() {
    var section = findSectionById(GS_ACTIVE_SECTION_ID);
    if (!section) {
        return;
    }

    var phase = GS_GRADE_MODAL_STATE.phase;
    var components = getDefaultGradeComponents(phase);
    var componentIndex = GS_GRADE_MODAL_STATE.componentIndex;

    if (componentIndex < 0 || componentIndex >= components.length) {
        componentIndex = 0;
        GS_GRADE_MODAL_STATE.componentIndex = 0;
    }

    var subtitle = document.getElementById('gsGradeModalSubtitle');
    var tabs = document.getElementById('gsGradeTabs');
    var tbody = document.getElementById('gsGradeModalBody');
    var q1Head = document.getElementById('gsGradeColQ1');
    var q2Head = document.getElementById('gsGradeColQ2');

    if (!subtitle || !tabs || !tbody || !q1Head || !q2Head) {
        return;
    }

    subtitle.textContent = section.section + ' - ' + section.courseCode + ' - ' + (phase === 'midterm' ? 'Midterm' : 'Final');
    tabs.innerHTML = buildGradeTabHtml(components, componentIndex);
    tbody.innerHTML = buildGradeRowsHtml(section.students, phase, componentIndex);

    q1Head.textContent = 'Q1 (20)';
    q2Head.textContent = 'Q2 (20)';

    bindGradeTabClick();
}

function openGradeModal(phase) {
    var modal = document.getElementById('gsGradeModal');
    if (!modal || !GS_ACTIVE_SECTION_ID) {
        return;
    }

    GS_GRADE_MODAL_STATE.phase = phase;
    GS_GRADE_MODAL_STATE.componentIndex = 0;
    renderGradeModalContent();

    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');
}

function closeGradeModal() {
    var modal = document.getElementById('gsGradeModal');
    if (!modal) {
        return;
    }

    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
}

function bindGradeTabClick() {
    var tabsWrap = document.getElementById('gsGradeTabs');
    if (!tabsWrap) {
        return;
    }

    var buttons = tabsWrap.querySelectorAll('.gs-grade-tab');
    for (var i = 0; i < buttons.length; i++) {
        buttons[i].addEventListener('click', function (event) {
            var index = parseInt(event.currentTarget.getAttribute('data-component-index'), 10);
            if (!isNaN(index)) {
                GS_GRADE_MODAL_STATE.componentIndex = index;
                renderGradeModalContent();
            }
        });
    }
}

function bindGradeModalEvents() {
    var midtermHeader = document.getElementById('gsMidtermHeader');
    var finalHeader = document.getElementById('gsFinalHeader');
    var modal = document.getElementById('gsGradeModal');
    var closeBtn = document.getElementById('gsGradeModalClose');

    if (midtermHeader) {
        midtermHeader.addEventListener('click', function () {
            openGradeModal('midterm');
        });
    }

    if (finalHeader) {
        finalHeader.addEventListener('click', function () {
            openGradeModal('final');
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', closeGradeModal);
    }

    if (modal) {
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeGradeModal();
            }
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeGradeModal();
        }
    });
}

function initGradingSheetPage() {
    renderSectionList();
    bindGradeModalEvents();

    var listBody = document.getElementById('gsListBody');
    if (!listBody) {
        return;
    }

    listBody.addEventListener('click', function (event) {
        var row = event.target.closest('tr[data-section-id]');
        if (!row) {
            return;
        }

        showDetailView(row.getAttribute('data-section-id'));
    });
}

window.showDetailView = showDetailView;
window.showListView = showListView;
window.handleViewList = handleViewList;
window.closeGradeModal = closeGradeModal;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initGradingSheetPage);
} else {
    initGradingSheetPage();
}
