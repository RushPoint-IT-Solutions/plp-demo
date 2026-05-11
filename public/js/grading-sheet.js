/* Grading Sheet Page – Midterm & Final only */

var GS_SECTIONS = [];
var GS_ACTIVE_SECTION_ID = null;
var GS_GRADE_MODAL_STATE = {
    phase: 'midterm',
    componentIndex: 0
};

var GS_PHASES = [
    { key: 'prelim', label: 'PRELIM' },
    { key: 'midterm', label: 'MIDTERM' },
    { key: 'final', label: 'FINAL' }
];

function gsSeedSections(serverSections) {
    if (serverSections && serverSections.length) {
        GS_SECTIONS = serverSections.map(function(sec, idx) {
            var students = (sec.students || []).map(function(st) {
                return {
                    id: st.id || 0,
                    studentNo: st.studentNo || '',
                    name: st.name || '',
                    fda: !!st.fda,
                    na: !!st.na,
                    prelim: st.prelim !== null && st.prelim !== undefined ? Number(st.prelim) : null,
                    midterm: st.midterm !== null && st.midterm !== undefined ? Number(st.midterm) : null,
                    final: st.final !== null && st.final !== undefined ? Number(st.final) : null,
                    cRating: st.cRating !== null && st.cRating !== undefined ? Number(st.cRating) : null,
                    fRating: st.fRating !== null && st.fRating !== undefined ? Number(st.fRating) : null,
                    remarks: st.remarks || ''
                };
            });
            return {
                id: sec.id || (idx + 1),
                section: sec.section || '-',
                courseCode: sec.courseCode || '-',
                description: sec.description || '-',
                faculty: sec.faculty || '-',
                midterm: sec.midterm || '-',
                final: sec.final || '-',
                approvedBy: sec.approvedBy || '-',
                courseFull: sec.courseFull || '-',
                schedule: sec.schedule || 'Room No. : TBA',
                status: sec.status || 'Submitted',
                statusCode: String(sec.statusCode || '').toUpperCase(),
                students: students
            };
        });
        return;
    }

    // Dummy data
    var dummyStudents = [
        { studentNo: '202310078', name: 'ALFARO, WHIELY PAULO GARCIA', fda: false, na: false, midterm: 92.47, final: 91.50, cRating: null, fRating: null, remarks: '' },
        { studentNo: '202211037', name: 'CIELO, MICA ELLA MATIENZO', fda: false, na: false, midterm: 85.17, final: 88.00, cRating: null, fRating: null, remarks: '' },
        { studentNo: '202310118', name: 'DE LEON, JON RICHARD CALMA', fda: false, na: false, midterm: 88.27, final: 90.25, cRating: null, fRating: null, remarks: '' },
        { studentNo: '202110227', name: 'ESPARZA JR., DANILO MARING', fda: false, na: false, midterm: 88.13, final: 90.42, cRating: null, fRating: null, remarks: '' },
        { studentNo: '202110243', name: 'DIZON, KEVIN LACANILAR', fda: false, na: false, midterm: 89.93, final: 90.42, cRating: null, fRating: null, remarks: '' },
        { studentNo: '202310194', name: 'FERNANDEZ, LIMARIE JEWEL MANULIT', fda: false, na: false, midterm: 91.88, final: 89.55, cRating: null, fRating: null, remarks: '' },
        { studentNo: '202110066', name: 'FLORES, GIANNA ANGELIA GALVAN', fda: false, na: false, midterm: 92.87, final: 91.23, cRating: null, fRating: null, remarks: '' },
        { studentNo: '202110169', name: 'GABRIEL, ARISHERINA REGEH SANTOS', fda: false, na: false, midterm: 91.50, final: 88.90, cRating: null, fRating: null, remarks: '' },
        { studentNo: '202209832', name: 'LINSON, STRINON MATTHEW TESALONA', fda: false, na: false, midterm: 82.55, final: 93.92, cRating: null, fRating: null, remarks: '' },
        { studentNo: '202310245', name: 'MAMAD, JAMAICA MALLARI', fda: false, na: false, midterm: 93.21, final: 95.25, cRating: null, fRating: null, remarks: '' },
        { studentNo: '202310210', name: 'MAHARAT, JUSTINE TRICIA CRUZ', fda: false, na: false, midterm: 97.48, final: 95.25, cRating: null, fRating: null, remarks: '' },
        { studentNo: '202310310', name: 'MANDAR, ALWIN LOYOLA', fda: false, na: false, midterm: 90.57, final: 95.32, cRating: null, fRating: null, remarks: '' },
        { studentNo: '202310408', name: 'MEDINA, KYLE JANZEN CABRAL', fda: false, na: false, midterm: 90.03, final: 88.30, cRating: null, fRating: null, remarks: '' },
        { studentNo: '202310066', name: 'MENDOZA, TRISTAN JOHN CORTEZ', fda: false, na: false, midterm: 87.70, final: 95.70, cRating: null, fRating: null, remarks: '' },
        { studentNo: '202210142', name: 'NUÑEZ, ARMHAMIDGE GARAPA', fda: false, na: false, midterm: 96.47, final: 96.25, cRating: null, fRating: null, remarks: '' },
        { studentNo: '202310124', name: 'SAMSON, ASHLEY JOKIS SANJOS', fda: false, na: false, midterm: 89.37, final: 90.15, cRating: null, fRating: null, remarks: '' },
        { studentNo: '202310405', name: 'TAVAS, JOSHUA CORDERO', fda: false, na: false, midterm: 91.03, final: 87.55, cRating: null, fRating: null, remarks: '' },
        { studentNo: '202310150', name: 'TOLENTINO, KRISTINE JADE CURA', fda: false, na: false, midterm: 96.43, final: 96.63, cRating: null, fRating: null, remarks: '' }
    ];

    GS_SECTIONS = [
        {
            id: 1, section: 'BSIM 3-A1 P19', courseCode: 'BMT2', description: 'STRATEGIC MANAGEMENT',
            faculty: 'GORDANCE, AIRA TOLET', midterm: '02/21/2026', final: '-',
            approvedBy: 'Registrar', courseFull: 'STRATEGIC MANAGEMENT', schedule: 'Room No. : BLDG. 3-102',
            status: 'Submitted',
            students: dummyStudents
        },
        {
            id: 2, section: 'BSIT 4-A', courseCode: 'CAP102', description: 'CAPSTONE PROJECT AND RESEARCH 2',
            faculty: 'DIAZ, JONNEL MARK', midterm: '02/21/2025', final: '02/21/2026',
            approvedBy: 'Admin 1', courseFull: 'CAPSTONE PROJECT AND RESEARCH 2', schedule: 'Room No. : TBA',
            status: 'Submitted',
            students: dummyStudents.slice(0, 8)
        },
        {
            id: 3, section: 'BSCS 4-A', courseCode: 'CAP102', description: 'CAPSTONE PROJECT AND RESEARCH 2',
            faculty: 'DIAZ, JONNEL MARK', midterm: '02/21/2026', final: '02/21/2026',
            approvedBy: 'Admin 1', courseFull: 'CAPSTONE PROJECT AND RESEARCH 2', schedule: 'Room No. : TBA',
            status: 'Submitted',
            students: dummyStudents.slice(0, 5)
        }
    ];
}

function findSectionById(id) {
    for (var i = 0; i < GS_SECTIONS.length; i++) {
        if (String(GS_SECTIONS[i].id) === String(id)) return GS_SECTIONS[i];
    }
    return null;
}


function gsEscapeHtml(value) {
    return String(value || '').replace(/[&<>"']/g, function (ch) {
        var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
        return map[ch];
    });
}

function gsGetCsrf() {
    return (document.querySelector('meta[name="csrf-token"]') || {}).content
        || (document.querySelector('input[name="_token"]') || {}).value
        || '';
}

function gsStatusBadge(status) {
    var label = status || 'Submitted for Dean Review';
    var normalized = String(label).toLowerCase();
    var className = 'gs-badge-submitted';
    if (normalized.indexOf('approved') !== -1 || normalized.indexOf('finalized') !== -1) className = 'gs-badge-approved';
    if (normalized.indexOf('returned') !== -1 || normalized.indexOf('rejected') !== -1) className = 'gs-badge-rejected';
    return '<span class="gs-badge ' + className + '">' + gsEscapeHtml(label) + '</span>';
}

function gsActionButtons(sec) {
    var statusCode = String(sec.statusCode || '').toUpperCase();
    if (statusCode === 'DEAN_APPROVED') {
        return '<div class="gs-action-btns">'
            + '<button type="button" class="gs-btn-approve" data-gs-action="finalized" '
            +   'data-id="' + sec.id + '" '
            +   'data-name="' + gsEscapeHtml(sec.description) + '" '
            +   'data-section="' + gsEscapeHtml(sec.section) + '" '
            +   'data-faculty="' + gsEscapeHtml(sec.faculty) + '">'
            +   'Registrar Finalize'
            + '</button>'
            + '</div>';
    }
    if (statusCode === 'REGISTRAR_FINALIZED' || statusCode === 'REJECTED') {
        return '<span class="text-muted">-</span>';
    }

    return '<div class="gs-action-btns">'
        + '<button type="button" class="gs-btn-approve" data-gs-action="dean_approved" '
        +   'data-id="' + sec.id + '" '
        +   'data-name="' + gsEscapeHtml(sec.description) + '" '
        +   'data-section="' + gsEscapeHtml(sec.section) + '" '
        +   'data-faculty="' + gsEscapeHtml(sec.faculty) + '">'
        +   'Dean Approve'
        + '</button>'
        + '<button type="button" class="gs-btn-reject" data-gs-action="rejected" '
        +   'data-id="' + sec.id + '" '
        +   'data-name="' + gsEscapeHtml(sec.description) + '" '
        +   'data-section="' + gsEscapeHtml(sec.section) + '" '
        +   'data-faculty="' + gsEscapeHtml(sec.faculty) + '">'
        +   'Reject'
        + '</button>'
        + '</div>';
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
            '<td>' + gsStatusBadge(s.status) + '</td>' +
            '<td>' + gsActionButtons(s) + '</td>' +
        '</tr>';
    }
    tbody.innerHTML = html;

    var pageInfo = document.getElementById('gsListPageInfo');
    if (pageInfo) pageInfo.textContent = 'Showing ' + GS_SECTIONS.length + ' sections';
}

function gsComputeRating(student) {
    var sum = 0, count = 0;
    if (student.prelim !== null && student.prelim !== undefined && !isNaN(Number(student.prelim))) {
        sum += Number(student.prelim); count++;
    }
    if (student.midterm !== null && student.midterm !== undefined && !isNaN(Number(student.midterm))) {
        sum += Number(student.midterm); count++;
    }
    if (student.final !== null && student.final !== undefined && !isNaN(Number(student.final))) {
        sum += Number(student.final); count++;
    }
    return count > 0 ? sum / count : null;
}

function gsComputeFRating(cRating) {
    if (cRating === null) return null;
    if (cRating >= 97) return 1.00;
    if (cRating >= 94) return 1.25;
    if (cRating >= 91) return 1.50;
    if (cRating >= 88) return 1.75;
    if (cRating >= 85) return 2.00;
    if (cRating >= 82) return 2.25;
    if (cRating >= 79) return 2.50;
    if (cRating >= 76) return 2.75;
    if (cRating >= 75) return 3.00;
    return 5.00;
}

function gsGetRemarks(fRating) {
    if (fRating === null) return '';
    return fRating <= 3.00 ? 'Passed' : 'Failed';
}

function gsFormatGrade(v) {
    if (v === null || v === undefined) return '<span class="gs-grade-na">N/A</span>';
    return Number(v).toFixed(2);
}

function showDetailView(id) {
    var sec = findSectionById(id);
    if (!sec) return;
    GS_ACTIVE_SECTION_ID = sec.id;

    document.getElementById('gsBannerSection').textContent = sec.section;
    document.getElementById('gsBannerCourse').textContent = sec.courseFull;
    document.getElementById('gsBannerProf').textContent = sec.faculty;
    document.getElementById('gsBannerSched').textContent = sec.schedule;

    var detailBody = document.getElementById('gsDetailBody');
    var html = '';
    for (var i = 0; i < sec.students.length; i++) {
        var st = sec.students[i];
        var cR = gsComputeRating(st);
        var fR = gsComputeFRating(cR);
        var remarks = gsGetRemarks(fR);
        var remarksClass = remarks === 'Passed' ? 'gs-remarks-passed' : (remarks === 'Failed' ? 'gs-remarks-failed' : '');

        html += '<tr>' +
            '<td class="gs-col-num">' + (i + 1) + '</td>' +
            '<td class="gs-col-studno">' + st.studentNo + '</td>' +
            '<td class="gs-col-name">' + st.name + '</td>' +
            '<td class="gs-col-flag"><input type="checkbox" class="gs-checkbox"' + (st.fda ? ' checked' : '') + '></td>' +
            '<td class="gs-col-flag"><input type="checkbox" class="gs-checkbox"' + (st.na ? ' checked' : '') + '></td>' +
            '<td class="gs-col-grade gs-phase-cell" data-phase="prelim">' + gsFormatGrade(st.prelim) + '</td>' +
            '<td class="gs-col-grade gs-phase-cell" data-phase="midterm">' + gsFormatGrade(st.midterm) + '</td>' +
            '<td class="gs-col-grade gs-phase-cell" data-phase="final">' + gsFormatGrade(st.final) + '</td>' +
            '<td class="gs-col-grade">' + (cR !== null ? cR.toFixed(2) : '<span class="gs-grade-na">N/A</span>') + '</td>' +
            '<td class="gs-col-grade">' + (fR !== null ? fR.toFixed(2) : '<span class="gs-grade-na">N/A</span>') + '</td>' +
            '<td class="gs-col-remarks ' + remarksClass + '">' + remarks + '</td>' +
        '</tr>';
    }
    detailBody.innerHTML = html;
    var detailPageInfo = document.getElementById('gsDetailPageInfo');
    if (detailPageInfo) detailPageInfo.textContent = 'Showing ' + sec.students.length + ' students';

    document.getElementById('gsListView').style.display = 'none';
    document.getElementById('gsDetailView').style.display = 'block';
}

function showListView() {
    closeGradeModal();
    document.getElementById('gsDetailView').style.display = 'none';
    document.getElementById('gsListView').style.display = 'block';
}

function handleViewList() {
    renderSectionList();
    showListView();
}

/* Grading Components */
function getDefaultGradeComponents(phase) {
    return [
        { label: 'WRITTEN/SEATWORK', weight: 20 },
        { label: 'PERFORMANCE TASK', weight: 25 },
        { label: 'SUMMATIVE MAJOR EXAM', weight: 55 }
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
        var base = st[phase];
        if (base === null || base === undefined || isNaN(Number(base))) base = 0;
        else base = Number(base);

        var q1 = Math.max(0, Math.min(20, Math.round((base / 100) * 20)));
        var variance = ((i + componentIndex) % 3) - 1;
        var q2 = Math.max(0, Math.min(20, q1 + variance));
        var pe = ((q1 + q2) / 40) * 100;

        html += '<tr>' +
            '<td>' + (i + 1) + '</td>' +
            '<td>' + st.studentNo + '</td>' +
            '<td>' + st.name + '</td>' +
            '<td><input type="number" class="gs-grade-input" min="0" max="100" step="1" value="' + q1 + '"></td>' +
            '<td><input type="number" class="gs-grade-input" min="0" max="100" step="1" value="' + q2 + '"></td>' +
            '<td><input type="text" class="gs-grade-input gs-grade-input-readonly" value="' + pe.toFixed(2) + '" readonly></td>' +
        '</tr>';
    }
    return html;
}

function renderGradeModalContent() {
    var section = findSectionById(GS_ACTIVE_SECTION_ID);
    if (!section) return;

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
    var phaseLabel = '';
    for (var p = 0; p < GS_PHASES.length; p++) {
        if (GS_PHASES[p].key === phase) { phaseLabel = GS_PHASES[p].label; break; }
    }

    if (subtitle) subtitle.textContent = section.section + ' - ' + section.courseCode + ' — ' + phaseLabel;
    if (tabs) tabs.innerHTML = buildGradeTabHtml(components, componentIndex);
    if (tbody) tbody.innerHTML = buildGradeRowsHtml(section.students, phase, componentIndex);

    if (q1Head) q1Head.textContent = 'Q1 (20)';
    if (q2Head) q2Head.textContent = 'Q2 (20)';

    bindGradeTabClick();
}

function openGradeModal(phase) {
    var modal = document.getElementById('gsGradeModal');
    if (!modal || !GS_ACTIVE_SECTION_ID) return;

    GS_GRADE_MODAL_STATE.phase = phase;
    GS_GRADE_MODAL_STATE.componentIndex = 0;
    renderGradeModalContent();

    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');
}

function closeGradeModal() {
    var modal = document.getElementById('gsGradeModal');
    if (!modal) return;
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
}

function bindGradeTabClick() {
    var tabsWrap = document.getElementById('gsGradeTabs');
    if (!tabsWrap) return;
    var buttons = tabsWrap.querySelectorAll('.gs-grade-tab');
    for (var i = 0; i < buttons.length; i++) {
        buttons[i].addEventListener('click', function(event) {
            var index = parseInt(event.currentTarget.getAttribute('data-component-index'), 10);
            if (!isNaN(index)) {
                GS_GRADE_MODAL_STATE.componentIndex = index;
                renderGradeModalContent();
            }
        });
    }
}

function bindGradeModalEvents() {
    var prelimHeader = document.getElementById('gsPrelimHeader');
    var midtermHeader = document.getElementById('gsMidtermHeader');
    var finalHeader = document.getElementById('gsFinalHeader');

    if (prelimHeader) {
        prelimHeader.addEventListener('click', function() { openGradeModal('prelim'); });
    }
    if (midtermHeader) {
        midtermHeader.addEventListener('click', function() { openGradeModal('midterm'); });
    }
    if (finalHeader) {
        finalHeader.addEventListener('click', function() { openGradeModal('final'); });
    }

    var closeBtn = document.getElementById('gsGradeModalClose');
    if (closeBtn) closeBtn.addEventListener('click', closeGradeModal);

    var modal = document.getElementById('gsGradeModal');
    if (modal) {
        modal.addEventListener('click', function(event) {
            if (event.target === modal) closeGradeModal();
        });
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') closeGradeModal();
    });
}


function doGradingAction(subjectId, action, name, section, faculty, btn) {
    if (action === 'dean_approved' || action === 'finalized') {
        var isFinalize = action === 'finalized';
        Swal.fire({
            title: isFinalize ? 'Finalize Grading Sheet?' : 'Dean Approve Grading Sheet?',
            html: 'You are about to <strong>' + (isFinalize ? 'finalize' : 'approve') + '</strong> the grades submitted by:<br><br>'
                + '<strong>' + gsEscapeHtml(name) + '</strong><br>'
                + '<span style="color:#6b7280;font-size:0.9rem;">'
                + gsEscapeHtml(section) + ' &bull; ' + gsEscapeHtml(faculty)
                + '</span>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#15803d',
            cancelButtonColor: '#6b7280',
            confirmButtonText: isFinalize ? 'Yes, Finalize' : 'Yes, Approve',
            cancelButtonText: 'Cancel',
        }).then(function (result) {
            if (!result.isConfirmed) return;
            gsPostAction(subjectId, action, '', name, section, btn);
        });

    } else {
        Swal.fire({
            title: 'Reject Grading Sheet?',
            html: 'The grades for <strong>' + gsEscapeHtml(name) + '</strong> '
                + '(<span style="color:#6b7280;">' + gsEscapeHtml(section) + '</span>) '
                + 'will be <strong>sent back</strong> to <strong>' + gsEscapeHtml(faculty)
                + '</strong> for revision.<br><br>'
                + '<label style="font-size:0.85rem;font-weight:600;display:block;text-align:left;margin-bottom:4px;">'
                + 'Reason (optional)</label>'
                + '<textarea id="swalRejectReason" class="swal2-textarea" '
                + 'placeholder="Enter reason for rejection..." style="font-size:0.88rem;"></textarea>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, Reject',
            cancelButtonText: 'Cancel',
            preConfirm: function () {
                return document.getElementById('swalRejectReason').value.trim();
            },
        }).then(function (result) {
            if (!result.isConfirmed) return;
            gsPostAction(subjectId, 'rejected', result.value || '', name, section, btn);
        });
    }
}

function gsPostAction(subjectId, action, remarks, name, section, btn) {
    btn.disabled = true;

    fetch(window.GS_ACTION_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': gsGetCsrf(),
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            subject_id: subjectId,
            action:     action,
            remarks:    remarks,
        }),
    })
    .then(function (res) { return res.json(); })
    .then(function (data) {
        if (!data.ok) {
            Swal.fire({
                title: 'Error',
                text: data.message || 'Action failed. Please try again.',
                icon: 'error',
                confirmButtonColor: '#15803d',
            });
            btn.disabled = false;
            return;
        }

        // Remove from local list — it's no longer Submitted
        GS_SECTIONS.forEach(function (s) {
            if (String(s.id) === String(subjectId)) {
                s.statusCode = String(data.status || '').toUpperCase();
                s.status = data.label || s.status;
                s.approvedBy = data.approvedBy || (action === 'rejected' ? 'Returned' : 'Updated');
            }
        });

        // Also sync window.GS_SERVER_SECTIONS if present
        if (window.GS_SERVER_SECTIONS) {
            window.GS_SERVER_SECTIONS.forEach(function (s) {
                if (String(s.id) === String(subjectId)) {
                    s.statusCode = String(data.status || '').toUpperCase();
                    s.status = data.label || s.status;
                }
            });
        }

        // Re-render the list
        renderSectionList();

        var isApproved = action === 'dean_approved' || action === 'finalized';
        Swal.fire({
            title: action === 'finalized' ? 'Grades Finalized!' : (isApproved ? 'Grades Approved!' : 'Grades Rejected'),
            html: '<strong>' + gsEscapeHtml(name) + '</strong> '
                + '(<span style="color:#6b7280;">' + gsEscapeHtml(section) + '</span>)<br><br>'
                + (isApproved
                    ? (action === 'finalized' ? 'Grades have been <strong>finalized</strong> successfully.' : 'Grades have been <strong>approved</strong> successfully.')
                    : 'Grades have been <strong>sent back</strong> to faculty for revision.'),
            icon: isApproved ? 'success' : 'info',
            confirmButtonColor: '#15803d',
            confirmButtonText: 'Done',
        });
    })
    .catch(function () {
        Swal.fire({
            title: 'Network Error',
            text: 'Something went wrong. Please try again.',
            icon: 'error',
            confirmButtonColor: '#15803d',
        });
        btn.disabled = false;
    });
}

function bindListBodyActions(listBody) {
    listBody.addEventListener('click', function (event) {

        var approveBtn = event.target.closest('.gs-btn-approve');
        if (approveBtn) {
            event.stopPropagation();
            doGradingAction(
                approveBtn.getAttribute('data-id'),
                approveBtn.getAttribute('data-gs-action') || 'dean_approved',
                approveBtn.getAttribute('data-name'),
                approveBtn.getAttribute('data-section'),
                approveBtn.getAttribute('data-faculty'),
                approveBtn
            );
            return;
        }

        var rejectBtn = event.target.closest('.gs-btn-reject');
        if (rejectBtn) {
            event.stopPropagation();
            doGradingAction(
                rejectBtn.getAttribute('data-id'),
                rejectBtn.getAttribute('data-gs-action') || 'rejected',
                rejectBtn.getAttribute('data-name'),
                rejectBtn.getAttribute('data-section'),
                rejectBtn.getAttribute('data-faculty'),
                rejectBtn
            );
            return;
        }

        var row = event.target.closest('tr[data-section-id]');
        if (row && !event.target.closest('button')) {
            showDetailView(row.getAttribute('data-section-id'));
        }
    });
}


function initGradingSheetPage() {
    var serverData = window.GS_SERVER_SECTIONS || null;
    gsSeedSections(serverData);
    renderSectionList();
    bindGradeModalEvents();

    var listBody = document.getElementById('gsListBody');
    if (listBody) {
        bindListBodyActions(listBody);
    }
}

window.showDetailView = showDetailView;
window.showListView = showListView;
window.handleViewList = handleViewList;
window.closeGradeModal = closeGradeModal;
window.openGradeModal = openGradeModal;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initGradingSheetPage);
} else {
    initGradingSheetPage();
}
