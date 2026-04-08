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
            '<td>' + (i + 1) + '</td>' +
            '<td>' + st.studentNo + '</td>' +
            '<td>' + st.name + '</td>' +
            '<td style="text-align:center;"><input type="checkbox" class="gs-checkbox"' + (st.fda ? ' checked' : '') + '></td>' +
            '<td style="text-align:center;"><input type="checkbox" class="gs-checkbox"' + (st.na ? ' checked' : '') + '></td>' +
            '<td>' + st.midterm.toFixed(2) + '</td>' +
            '<td>' + st.final.toFixed(2) + '</td>' +
            '<td>' + st.cRating.toFixed(2) + '</td>' +
            '<td>' + st.fRating.toFixed(2) + '</td>' +
            '<td class="gs-remarks-passed">' + st.remarks + '</td>' +
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

    detailView.style.display = 'none';
    listView.style.display = 'block';
}

function handleViewList() {
    renderSectionList();
    showListView();
}

function initGradingSheetPage() {
    renderSectionList();

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

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initGradingSheetPage);
} else {
    initGradingSheetPage();
}
