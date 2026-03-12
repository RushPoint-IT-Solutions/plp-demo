/* ── Grading Sheet Page ── */

var GS_SECTIONS = [
    {
        id: 1, section: 'BSIT-4A', courseCode: 'CAP 102', description: 'CAPSTONE PROJECT AND RESEARCH 2',
        faculty: 'DIAZ, JONNEL MARK', prelim: '02/21/2026', midterm: '02/21/2025', preFinal: '02/21/2026', finalized: '02/21/2026',
        approvedBy: 'Admin 1', courseFull: 'Bachelor Of Sience In Information Technology', schedule: 'Room No. : TBA',
        students: [
            { studentNo: '2223A8141', name: 'ABILA, MARK V.', fda: false, na: false, prelim: 86.50, midterm: 85.00, preFinal: 85.00, finals: 85.00, cRating: 85.30, fRating: 2.25, remarks: 'Passed' },
            { studentNo: '2223A8142', name: 'ACEDO, VINCENT', fda: false, na: false, prelim: 86.50, midterm: 85.00, preFinal: 85.00, finals: 85.00, cRating: 85.30, fRating: 2.25, remarks: 'Passed' }
        ]
    },
    {
        id: 2, section: 'BSCS- 4A', courseCode: 'CAP 102', description: 'CAPSTONE PROJECT AND RESEARCH 2',
        faculty: 'DIAZ, JONNEL MARK', prelim: '02/21/2026', midterm: '02/21/2026', preFinal: '02/21/2026', finalized: '02/21/2026',
        approvedBy: 'Admin 1', courseFull: 'Bachelor Of Science In Computer Science', schedule: 'Room No. : TBA',
        students: [
            { studentNo: '2223A8137', name: 'BARES, MARK JAY', fda: false, na: false, prelim: 86.50, midterm: 85.00, preFinal: 85.00, finals: 85.00, cRating: 85.30, fRating: 2.25, remarks: 'Passed' },
            { studentNo: '2223A8139', name: 'DELA CRUZ, JUAN', fda: false, na: false, prelim: 86.50, midterm: 85.00, preFinal: 85.00, finals: 85.00, cRating: 85.30, fRating: 2.25, remarks: 'Passed' },
            { studentNo: '2223A8139', name: 'DELA CRUZ, JUAN', fda: false, na: false, prelim: 86.50, midterm: 85.00, preFinal: 85.00, finals: 85.00, cRating: 85.30, fRating: 2.25, remarks: 'Passed' }
        ]
    }
];

/* ── Render section list (View 1) ── */
function renderSectionList() {
    var tbody = document.getElementById('gsListBody');
    var html = '';
    for (var i = 0; i < GS_SECTIONS.length; i++) {
        var s = GS_SECTIONS[i];
        html += '<tr class="gs-section-row" onclick="showDetailView(' + s.id + ')" style="cursor:pointer;">' +
            '<td>' + (i + 1) + '</td>' +
            '<td class="gs-section-link">' + s.section + '</td>' +
            '<td>' + s.courseCode + '</td>' +
            '<td>' + s.description + '</td>' +
            '<td>' + s.faculty + '</td>' +
            '<td class="gs-date-cell">' + s.prelim + '</td>' +
            '<td class="gs-date-cell">' + s.midterm + '</td>' +
            '<td class="gs-date-cell">' + s.preFinal + '</td>' +
            '<td class="gs-date-cell">' + s.finalized + '</td>' +
            '<td>' + s.approvedBy + '</td>' +
        '</tr>';
    }
    tbody.innerHTML = html;
    document.getElementById('gsListPageInfo').textContent = 'Showing ' + GS_SECTIONS.length + ' sections';
}

/* ── Show detail view (View 2) ── */
function showDetailView(id) {
    var sec = GS_SECTIONS.find(function(s) { return s.id === id; });
    if (!sec) return;

    document.getElementById('gsBannerSection').textContent = sec.section;
    document.getElementById('gsBannerCourse').textContent = sec.courseFull;
    /* Format: "DIAZ, JONNEL MARK" → "Diaz, Jonnel Mark" */
    var profName = sec.faculty.replace(/\w\S*/g, function(t) { return t.charAt(0).toUpperCase() + t.slice(1).toLowerCase(); });
    document.getElementById('gsBannerProf').textContent = profName;
    document.getElementById('gsBannerSched').textContent = sec.schedule;

    var tbody = document.getElementById('gsDetailBody');
    var html = '';
    for (var i = 0; i < sec.students.length; i++) {
        var st = sec.students[i];
        html += '<tr>' +
            '<td>' + (i + 1) + '</td>' +
            '<td>' + st.studentNo + '</td>' +
            '<td>' + st.name + '</td>' +
            '<td style="text-align:center;"><input type="checkbox" class="gs-checkbox"' + (st.fda ? ' checked' : '') + '></td>' +
            '<td style="text-align:center;"><input type="checkbox" class="gs-checkbox"' + (st.na ? ' checked' : '') + '></td>' +
            '<td>' + st.prelim.toFixed(2) + '</td>' +
            '<td>' + st.midterm.toFixed(2) + '</td>' +
            '<td>' + st.preFinal.toFixed(2) + '</td>' +
            '<td>' + st.finals.toFixed(2) + '</td>' +
            '<td>' + st.cRating.toFixed(2) + '</td>' +
            '<td>' + st.fRating.toFixed(2) + '</td>' +
            '<td class="gs-remarks-passed">' + st.remarks + '</td>' +
        '</tr>';
    }
    tbody.innerHTML = html;
    document.getElementById('gsDetailPageInfo').textContent = 'Showing ' + sec.students.length + ' students';

    document.getElementById('gsListView').style.display = 'none';
    document.getElementById('gsDetailView').style.display = 'block';
}

/* ── Back to list ── */
function showListView() {
    document.getElementById('gsDetailView').style.display = 'none';
    document.getElementById('gsListView').style.display = 'block';
}

/* ── View List button (just re-renders) ── */
function handleViewList() {
    renderSectionList();
    showListView();
}

/* ── Init ── */
renderSectionList();
