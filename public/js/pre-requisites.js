/* ── Pre-requisites JS ── */

/* Dummy curriculum data keyed by course */
var CURRICULUM = {
    BSIT: {
        name: 'Bachelor of Science in Information Technology',
        years: [
            {
                label: '1st Year',
                semesters: [
                    {
                        label: 'First Semester',
                        subjects: [
                            { code: 'CC101', desc: 'Introduction to Computing',        units: 3, pre: 'None', co: 'None', equiv: 'None' },
                            { code: 'CC102', desc: 'Computer Programming 1',           units: 5, pre: 'None', co: 'None', equiv: 'None' },
                            { code: 'GE1',   desc: 'Understanding The Self',           units: 3, pre: 'None', co: 'None', equiv: 'None' },
                            { code: 'GE2',   desc: 'Gender and Society',               units: 3, pre: 'None', co: 'None', equiv: 'None' },
                            { code: 'GE3',   desc: 'Readings in The Philippine History',units: 3, pre: 'None', co: 'None', equiv: 'None' }
                        ]
                    },
                    { label: 'Second Semester', subjects: [] },
                    { label: 'Third Semester',  subjects: [] }
                ]
            },
            {
                label: '2nd Year',
                semesters: [
                    { label: 'First Semester',  subjects: [] },
                    { label: 'Second Semester', subjects: [] },
                    { label: 'Third Semester',  subjects: [] }
                ]
            },
            {
                label: '3rd Year',
                semesters: [
                    { label: 'First Semester',  subjects: [] },
                    { label: 'Second Semester', subjects: [] }
                ]
            },
            {
                label: '4th Year',
                semesters: [
                    { label: 'First Semester',  subjects: [] },
                    { label: 'Second Semester', subjects: [] }
                ]
            }
        ]
    }
};

/* All available subjects for the search panels */
var ALL_SUBJECTS = [
    { code: 'PATHFIT1', desc: 'BSIT: Movement Enhancement' },
    { code: 'GE101',   desc: 'Gender and Society' },
    { code: 'GE41',    desc: 'Mathematics in the Modern World' },
    { code: 'NSTP 1',  desc: 'National Service Training Program' },
    { code: 'NSTP101B',desc: 'NSTP-CWTS-1' },
    { code: 'CC101',   desc: 'Introduction to Computing' },
    { code: 'CC102',   desc: 'Computer Programming 1' },
    { code: 'GE1',     desc: 'Understanding The Self' },
    { code: 'GE2',     desc: 'Gender and Society' },
    { code: 'GE3',     desc: 'Readings in The Philippine History' },
    { code: 'GCC102',  desc: 'BSIT: Movement Enhancement' }
];

/* Currently-editing subject state */
var currentSubject = null;
var selectedPre = [];
var selectedCo  = [];
var selectedEq  = [];

/* ═══════════ VIEW 1 → VIEW 2: Load List ═══════════ */
function loadPrereqList() {
    var courseKey = document.getElementById('prereqCourse').value;
    var course = CURRICULUM[courseKey];
    if (!course) {
        /* Show empty for courses without data */
        course = { name: document.getElementById('prereqCourse').selectedOptions[0].text, years: [] };
    }

    document.getElementById('prereqProgramTitle').textContent = course.name.toUpperCase();
    var container = document.getElementById('prereqListContent');
    container.innerHTML = '';

    course.years.forEach(function (year) {
        var yearHtml = '<div class="prereq-year-block">';
        yearHtml += '<div class="prereq-year-label">' + year.label + '</div>';

        year.semesters.forEach(function (sem) {
            yearHtml += '<div class="prereq-sem-label">' + sem.label + '</div>';
            yearHtml += '<div class="prereq-table-wrap">';
            yearHtml += '<table class="prereq-table">';
            yearHtml += '<thead><tr><th>Subject Code</th><th>Description</th><th>Credited Units</th><th>Pre-requisite</th><th>Co-requisite</th><th>Equivalent Subject</th></tr></thead>';
            yearHtml += '<tbody>';

            if (sem.subjects.length === 0) {
                yearHtml += '<tr><td colspan="6" class="prereq-empty-msg">No List of Subject(s) yet for this Year Level Semester...</td></tr>';
            } else {
                sem.subjects.forEach(function (s) {
                    yearHtml += '<tr class="prereq-row" data-code="' + s.code + '" data-desc="' + s.desc + '" onclick="openSubjectDetail(\'' + s.code + '\', \'' + escapeAttr(s.desc) + '\')">';
                    yearHtml += '<td>' + s.code + '</td>';
                    yearHtml += '<td style="text-align:left;">' + s.desc + '</td>';
                    yearHtml += '<td>' + s.units + '</td>';
                    yearHtml += '<td>' + s.pre + '</td>';
                    yearHtml += '<td>' + s.co + '</td>';
                    yearHtml += '<td>' + s.equiv + '</td>';
                    yearHtml += '</tr>';
                });
            }

            yearHtml += '</tbody></table></div>';
        });

        yearHtml += '</div>';
        container.innerHTML += yearHtml;
    });

    document.getElementById('prereqListView').style.display = 'block';
    document.getElementById('prereqDetailView').style.display = 'none';
}

function escapeAttr(str) {
    return str.replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

/* ═══════════ VIEW 2 → VIEW 3: Open Subject Detail ═══════════ */
function openSubjectDetail(code, desc) {
    currentSubject = { code: code, desc: desc };
    selectedPre = [];
    selectedCo  = [];
    selectedEq  = [];

    document.getElementById('prereqDetailCode').textContent = code;
    document.getElementById('prereqDetailName').textContent = desc.toUpperCase();

    // Populate available lists
    renderAvailableList('pre');
    renderAvailableList('co');
    renderAvailableList('eq');
    renderSelectedList('pre');
    renderSelectedList('co');
    renderSelectedList('eq');

    document.getElementById('prereqListView').style.display = 'none';
    document.getElementById('prereqDetailView').style.display = 'block';
}

/* ═══════════ Available / Selected Lists ═══════════ */
function renderAvailableList(type) {
    var searchVal = '';
    if (type === 'pre') searchVal = document.getElementById('prereqSearchPre').value.toLowerCase();
    if (type === 'co')  searchVal = document.getElementById('prereqSearchCo').value.toLowerCase();
    if (type === 'eq')  searchVal = document.getElementById('prereqSearchEq').value.toLowerCase();

    var selected = type === 'pre' ? selectedPre : type === 'co' ? selectedCo : selectedEq;
    var containerId = type === 'pre' ? 'prereqAvailPre' : type === 'co' ? 'prereqAvailCo' : 'prereqAvailEq';
    var container = document.getElementById(containerId);
    var html = '';

    ALL_SUBJECTS.forEach(function (s) {
        // Skip if already selected
        if (selected.indexOf(s.code) !== -1) return;
        // Skip if current subject
        if (s.code === currentSubject.code) return;
        // Filter by search
        var text = (s.code + ' ' + s.desc).toLowerCase();
        if (searchVal && text.indexOf(searchVal) === -1) return;

        html += '<div class="prereq-avail-item">';
        html += '<span>1-A (' + s.code + ') ' + s.desc + '</span>';
        html += '<button type="button" class="prereq-arrow-btn" onclick="addToSelected(\'' + type + '\', \'' + s.code + '\')">';
        html += '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>';
        html += '</button>';
        html += '</div>';
    });

    if (!html) {
        html = '<div class="prereq-avail-empty">-select pre-requisite subject here-</div>';
    }

    container.innerHTML = html;
}

function renderSelectedList(type) {
    var selected = type === 'pre' ? selectedPre : type === 'co' ? selectedCo : selectedEq;
    var containerId = type === 'pre' ? 'prereqSelPre' : type === 'co' ? 'prereqSelCo' : 'prereqSelEq';
    var container = document.getElementById(containerId);

    if (selected.length === 0) {
        container.innerHTML = '';
        return;
    }

    var html = '';
    selected.forEach(function (code) {
        var subj = ALL_SUBJECTS.find(function (s) { return s.code === code; });
        if (!subj) return;
        html += '<div class="prereq-sel-item">';
        html += '<span>' + subj.code + ' - ' + subj.desc + '</span>';
        html += '<button type="button" class="prereq-remove-btn" onclick="removeFromSelected(\'' + type + '\', \'' + code + '\')">&times;</button>';
        html += '</div>';
    });
    container.innerHTML = html;
}

function addToSelected(type, code) {
    var arr = type === 'pre' ? selectedPre : type === 'co' ? selectedCo : selectedEq;
    if (arr.indexOf(code) === -1) arr.push(code);
    renderAvailableList(type);
    renderSelectedList(type);
}

function removeFromSelected(type, code) {
    var arr = type === 'pre' ? selectedPre : type === 'co' ? selectedCo : selectedEq;
    var idx = arr.indexOf(code);
    if (idx !== -1) arr.splice(idx, 1);
    // Sync the source array
    if (type === 'pre') selectedPre = arr;
    if (type === 'co')  selectedCo = arr;
    if (type === 'eq')  selectedEq = arr;
    renderAvailableList(type);
    renderSelectedList(type);
}

function filterPrereqAvailable(type) {
    renderAvailableList(type);
}

/* ═══════════ Actions ═══════════ */
function savePrereqDetail() {
    document.getElementById('prereqDetailView').style.display = 'none';
    document.getElementById('prereqListView').style.display = 'block';
    showRegistrarToast('Pre-requisites saved successfully.', 'success');
}

function downloadPrereqPDF() {
    showRegistrarToast('PDF download started.', 'success');
}
