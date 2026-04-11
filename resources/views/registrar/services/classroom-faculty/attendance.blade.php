@extends('layouts.registrar')

@section('title', 'PLP - Attendance')
@section('page-title', 'ATTENDANCE')
@section('body-class', 'page-services-attendance')

@section('content')
<div class="pf-page">
    <div class="svc-filter-panel">
        <div class="svc-filter-grid">
            <div class="svc-filter-item">
                <span class="app-filter-label">School Year</span>
                <select id="attSchoolYear" class="app-filter-select" style="width:100%;">
                    <option value="" selected>All school years</option>
                    <option value="2025-2026">2025-2026</option>
                    <option value="2024-2025">2024-2025</option>
                    <option value="2023-2024">2023-2024</option>
                </select>
            </div>

            <div class="svc-filter-item">
                <span class="app-filter-label">Semester</span>
                <select id="attSemester" class="app-filter-select" style="width:100%;">
                    <option value="" selected>All semesters</option>
                    <option value="First">First</option>
                    <option value="Second">Second</option>
                    <option value="Summer">Summer</option>
                </select>
            </div>

            <div class="svc-filter-item svc-filter-professor">
                <span class="app-filter-label">Professor</span>
                <select id="attProfessor" class="app-filter-select" style="width:100%;">
                    <option value="" selected>All professors</option>
                    <option value="Diaz, Jonnel">Diaz, Jonnel</option>
                    <option value="Dela Cruz, Juan">Dela Cruz, Juan</option>
                    <option value="Santos, Maria">Santos, Maria</option>
                </select>
            </div>

            <div class="svc-filter-item">
                <span class="app-filter-label">Year Level</span>
                <select id="attYearLevel" class="app-filter-select" style="width:100%;">
                    <option value="" selected>All year levels</option>
                    <option value="First">First</option>
                    <option value="Second">Second</option>
                    <option value="Third">Third</option>
                    <option value="Fourth">Fourth</option>
                </select>
            </div>

            <div class="svc-filter-item">
                <span class="app-filter-label">Section</span>
                <select id="attSection" class="app-filter-select" style="width:100%;">
                    <option value="" selected>All sections</option>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                </select>
            </div>

            <div class="svc-filter-action">
                <button type="button" class="pf-btn-new" onclick="runAttendanceSearch()">Search</button>
            </div>
        </div>
    </div>

    <div id="attAssignmentWrap" class="student-table-wrapper table-responsive att-assignment-wrap">
        <table class="app-table" id="attAssignmentTable">
            <thead>
                <tr>
                    <th>SY</th>
                    <th>Semester</th>
                    <th>Section</th>
                    <th>Year Level</th>
                    <th>Professor</th>
                </tr>
            </thead>
            <tbody id="attAssignmentBody"></tbody>
        </table>
        <div id="attAssignmentEmpty" class="att-assignment-empty">
            No matching schedule found.
        </div>
    </div>

    <div id="attResult" style="display:none;">
        <div class="att-summary-card">
            <div class="att-summary-meta">
                <div class="att-summary-pill"><span class="att-summary-label">Section</span><span class="att-summary-value" id="attSummarySection">-</span></div>
                <div class="att-summary-pill"><span class="att-summary-label">Adviser</span><span class="att-summary-value" id="attSummaryAdviser">-</span></div>
            </div>
            <button type="button" class="att-back-btn" onclick="showAttendanceAssignments()">&larr; Back to List</button>
        </div>

        <div class="student-table-wrapper table-responsive">
            <table class="att-table">
                <colgroup>
                    <col class="att-col-index">
                    <col class="att-col-name">
                    <col class="att-col-metric"><col class="att-col-metric">
                    <col class="att-col-metric"><col class="att-col-metric">
                    <col class="att-col-metric"><col class="att-col-metric">
                    <col class="att-col-metric"><col class="att-col-metric">
                    <col class="att-col-metric"><col class="att-col-metric">
                    <col class="att-col-summary">
                </colgroup>
                <thead>
                    <tr class="att-thead-top">
                        <th rowspan="2">#</th>
                        <th rowspan="2" class="att-name-head">Name</th>
                        <th colspan="2">Jan</th>
                        <th colspan="2">Feb</th>
                        <th colspan="2">Mar</th>
                        <th colspan="2">Nov</th>
                        <th colspan="2">Dec</th>
                        <th rowspan="2">Total No. Of Days</th>
                    </tr>
                    <tr class="att-thead-sub">
                        <th class="att-sub-metric">P</th><th class="att-sub-metric">T</th>
                        <th class="att-sub-metric">P</th><th class="att-sub-metric">T</th>
                        <th class="att-sub-metric">P</th><th class="att-sub-metric">T</th>
                        <th class="att-sub-metric">P</th><th class="att-sub-metric">T</th>
                        <th class="att-sub-metric">P</th><th class="att-sub-metric">T</th>
                    </tr>
                </thead>
                <tbody id="attDetailBody"></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var attendanceRows = [
    {
        schoolYear: '2025-2026',
        semester: 'Second',
        section: 'A',
        yearLevel: 'Fourth',
        professor: 'Diaz, Jonnel',
        students: [
            { name: 'Bares, Mark Jay', metrics: [1, 1, 1, 1, 1, 1, 1, 1, 1, 1], total: 10 },
            { name: 'Austero, Andrea Jone', metrics: [1, 1, 1, 0, 1, 1, 1, 1, 0, 1], total: 8 },
            { name: 'Mendoza, Carlo', metrics: [1, 1, 0, 1, 1, 1, 1, 0, 1, 1], total: 8 },
            { name: 'Santos, Princess Mae', metrics: [1, 1, 1, 1, 1, 0, 1, 1, 1, 0], total: 8 }
        ]
    },
    {
        schoolYear: '2025-2026',
        semester: 'Second',
        section: 'B',
        yearLevel: 'Fourth',
        professor: 'Diaz, Jonnel',
        students: [
            { name: 'Cruz, Paula Mae', metrics: [1, 1, 0, 1, 1, 1, 0, 1, 0, 1], total: 7 },
            { name: 'Torres, John Michael', metrics: [0, 1, 1, 1, 0, 1, 1, 1, 0, 1], total: 7 },
            { name: 'Reyes, Angela', metrics: [1, 1, 1, 1, 1, 1, 1, 1, 1, 1], total: 10 },
            { name: 'Lim, Joshua', metrics: [1, 0, 1, 1, 1, 0, 1, 1, 1, 0], total: 7 }
        ]
    },
    {
        schoolYear: '2025-2026',
        semester: 'Second',
        section: 'C',
        yearLevel: 'Fourth',
        professor: 'Diaz, Jonnel',
        students: [
            { name: 'De Guzman, Francine', metrics: [1, 1, 1, 1, 1, 1, 1, 0, 1, 1], total: 9 },
            { name: 'Lopez, Miguel', metrics: [1, 1, 1, 0, 1, 1, 1, 1, 1, 0], total: 8 },
            { name: 'Manalo, Trisha', metrics: [1, 0, 1, 1, 1, 0, 1, 1, 1, 1], total: 8 }
        ]
    },
    {
        schoolYear: '2025-2026',
        semester: 'Second',
        section: 'C',
        yearLevel: 'Third',
        professor: 'Dela Cruz, Juan',
        students: [
            { name: 'Garcia, Angelica', metrics: [1, 1, 1, 1, 0, 1, 1, 1, 1, 0], total: 8 },
            { name: 'Navarro, Bryan', metrics: [1, 1, 0, 1, 1, 1, 0, 1, 1, 1], total: 8 },
            { name: 'Ramos, Cedric', metrics: [0, 1, 1, 0, 1, 1, 1, 1, 1, 1], total: 8 }
        ]
    },
    {
        schoolYear: '2024-2025',
        semester: 'First',
        section: 'A',
        yearLevel: 'Second',
        professor: 'Santos, Maria',
        students: [
            { name: 'Villanueva, Abby', metrics: [1, 1, 1, 1, 1, 1, 1, 1, 1, 1], total: 10 },
            { name: 'Pascual, John Ray', metrics: [1, 1, 1, 0, 1, 1, 1, 1, 1, 0], total: 8 },
            { name: 'Aquino, Jessa', metrics: [1, 1, 0, 1, 1, 1, 0, 1, 1, 1], total: 8 }
        ]
    }
];

var selectedAttendanceRow = null;
var currentAttendanceRows = [];

function getAttendanceFilters() {
    return {
        schoolYear: (document.getElementById('attSchoolYear') && document.getElementById('attSchoolYear').value) || '',
        semester: (document.getElementById('attSemester') && document.getElementById('attSemester').value) || '',
        professor: (document.getElementById('attProfessor') && document.getElementById('attProfessor').value) || '',
        yearLevel: (document.getElementById('attYearLevel') && document.getElementById('attYearLevel').value) || '',
        section: (document.getElementById('attSection') && document.getElementById('attSection').value) || ''
    };
}

function matchesAttendanceFilters(row, filters) {
    return (!filters.schoolYear || row.schoolYear === filters.schoolYear)
        && (!filters.semester || row.semester === filters.semester)
        && (!filters.professor || row.professor === filters.professor)
        && (!filters.yearLevel || row.yearLevel === filters.yearLevel)
        && (!filters.section || row.section === filters.section);
}

function renderAttendanceAssignments() {
    var body = document.getElementById('attAssignmentBody');
    var empty = document.getElementById('attAssignmentEmpty');
    if (!body || !empty) return;

    var filters = getAttendanceFilters();
    var filteredRows = attendanceRows.filter(function (row) {
        return matchesAttendanceFilters(row, filters);
    });
    currentAttendanceRows = filteredRows.slice();

    body.innerHTML = '';
    if (!filteredRows.length) {
        empty.style.display = 'block';
        selectedAttendanceRow = null;
        hideAttendanceDetail();
        return;
    }

    empty.style.display = 'none';
    filteredRows.forEach(function (row, index) {
        var tr = document.createElement('tr');
        tr.setAttribute('data-att-row-index', String(index));
        if (selectedAttendanceRow === row) {
            tr.style.backgroundColor = '#eef8f2';
        }
        tr.innerHTML = '' +
            '<td>' + row.schoolYear + '</td>' +
            '<td>' + row.semester + '</td>' +
            '<td>' + row.section + '</td>' +
            '<td>' + row.yearLevel + '</td>' +
            '<td>' + row.professor + '</td>';
        body.appendChild(tr);
    });

    if (selectedAttendanceRow && !filteredRows.some(function (row) { return row === selectedAttendanceRow; })) {
        selectedAttendanceRow = null;
        hideAttendanceDetail();
    }
}

function renderAttendanceDetail(row) {
    var assignmentWrap = document.getElementById('attAssignmentWrap');
    var result = document.getElementById('attResult');
    var detailBody = document.getElementById('attDetailBody');
    var summarySection = document.getElementById('attSummarySection');
    var summaryAdviser = document.getElementById('attSummaryAdviser');
    if (!result || !detailBody || !summarySection || !summaryAdviser) return;

    summarySection.textContent = row.section;
    summaryAdviser.textContent = row.professor;
    detailBody.innerHTML = '';

    row.students.forEach(function (student, index) {
        var tr = document.createElement('tr');
        var cells = [index + 1, student.name]
            .concat(student.metrics || [])
            .concat([student.total || 0]);

        tr.innerHTML = cells.map(function (value) {
            return '<td>' + value + '</td>';
        }).join('');
        detailBody.appendChild(tr);
    });

    if (assignmentWrap) assignmentWrap.style.display = 'none';
    result.style.display = 'block';
}

function hideAttendanceDetail() {
    var assignmentWrap = document.getElementById('attAssignmentWrap');
    var result = document.getElementById('attResult');
    var detailBody = document.getElementById('attDetailBody');
    if (detailBody) detailBody.innerHTML = '';
    if (result) result.style.display = 'none';
    if (assignmentWrap) assignmentWrap.style.display = 'block';
}

function showAttendanceAssignments() {
    hideAttendanceDetail();
    renderAttendanceAssignments();
}

function runAttendanceSearch() {
    hideAttendanceDetail();
    selectedAttendanceRow = null;
    renderAttendanceAssignments();
}

document.addEventListener('DOMContentLoaded', function () {
    var filterIds = ['attSchoolYear', 'attSemester', 'attProfessor', 'attYearLevel', 'attSection'];
    filterIds.forEach(function (id) {
        var select = document.getElementById(id);
        if (select) {
            select.addEventListener('change', runAttendanceSearch);
        }
    });

    var assignmentBody = document.getElementById('attAssignmentBody');
    if (assignmentBody) {
        assignmentBody.addEventListener('click', function (event) {
            var rowEl = event.target.closest('tr[data-att-row-index]');
            if (!rowEl) return;

            var idx = parseInt(rowEl.getAttribute('data-att-row-index'), 10);
            if (isNaN(idx) || !currentAttendanceRows[idx]) return;

            selectedAttendanceRow = currentAttendanceRows[idx];
            renderAttendanceDetail(selectedAttendanceRow);
        });
    }

    renderAttendanceAssignments();
    hideAttendanceDetail();
});
</script>
@endpush
